<?php
class Ctf extends mysqli {
	function __construct() {
		parent::__construct(__HOST__, __USER__, __PASS__, __NAME__);
	}

    private function get_config() {
        $result = $this->query('SELECT * FROM mun_config');
        $config = $result->fetch_array(MYSQLI_ASSOC);
        return $config;
    }

    private function get_solve_cnt($prob_no) {
        $prob_no = (int)$prob_no;
        $result = $this->query("SELECT count(*) FROM mun_solves WHERE prob_no='{$prob_no}'");
        $solve_cnt = (int)$result->fetch_array(MYSQLI_NUM)[0];

        return $solve_cnt;
    }

    private function get_dynamic_point($prob_no) {
        $config = $this->get_config();
        $max_point = (int)$config['max_point'];
        $min_point = (int)$config['min_point'];

        $solve_cnt = $this->get_solve_cnt($prob_no);
        $retval = [];

        $prob_point = (int)round($min_point + ($max_point - $min_point) / (1 + (max(0, ($solve_cnt) - 1) / 4.0467890) ** 3.84));
        $prob_point = max($prob_point, $min_point);

        return $prob_point;
    }

	function check_flag($no, $flag, $username) {
		$no = (int)$no;
        $flag = addslashes($flag);
		$username = addslashes($username);
		$userip = $_SERVER['REMOTE_ADDR'];
        
        $retval = array(
            'result' => false,
            'message' => 'Incorrect flag!',
        );

        $config = $this->get_config();
        $end_time = (int)strtotime($config['end_time']);
        if(time() >= $end_time) {
            $retval = array(
                'result' => false,
                'message' => 'The CTF has ended!',
            );
            return json_encode($retval);
        }

        // 세션만 살아있는 팀일 수 있다, 삭제된 팀이면 인증 안되도록 실제 DB에 존재하는 유저인지 체크
        $result = $this->query("SELECT * FROM mun_users WHERE BINARY username='{$username}'");
        if(!$result->fetch_array(MYSQLI_NUM)) {
            $retval['message'] = 'Please login again.';
            $retval['logout'] = true;
            return json_encode($retval);
        }

        // 문제 이름 가져오기
		$result = $this->query("SELECT name FROM mun_probs WHERE no='{$no}'");
		$fetch = $result->fetch_array(MYSQLI_ASSOC);
		$prob_name = addslashes($fetch['name']);

		// 인증 로그 업데이트
		$this->query("INSERT INTO mun_auth_logs VALUES (NULL, '{$username}', '{$prob_name}', '{$userip}', '{$flag}', now())");
		$result = $this->query("SELECT * FROM mun_probs WHERE no='{$no}' AND BINARY flag='{$flag}' AND open=1");

		if($fetch = $result->fetch_array(MYSQLI_ASSOC)) { // 정답 플래그
			$teamname = addslashes($_SESSION['teamname']);
            $prob_no = (int)$fetch['no'];

            if($username === __ADMIN__) { // admin은 플래그 체크만 해주고 끝
                $retval['result'] = true;
                $retval['message'] = 'This flag is correct.';
                return json_encode($retval);
            }

            // 인증 중복체크
			$result = $this->query("SELECT * FROM mun_solves WHERE prob_no='{$prob_no}' AND BINARY teamname='{$teamname}'");
			if($result->fetch_array(MYSQLI_ASSOC)) {
                $retval['message'] = 'Already solved!'; // 이미 인증한 문제
            }
            else { // 인증 처리
                $this->query("INSERT INTO mun_solves VALUES (NULL, '{$prob_no}', '{$teamname}', '{$username}', now())"); // 푼 문제에 추가
                $this->query("UPDATE mun_users SET last_auth=now() WHERE BINARY username='{$username}'"); // 인증 시간 업데이트

                $new_point = $this->get_dynamic_point($prob_no);
                $this->query("UPDATE mun_probs SET point='{$new_point}' WHERE no='{$prob_no}'"); // 문제 점수 업데이트
                
                $retval['result'] = true;
                $retval['message'] = "You solved the {$prob_name} problem, congrats!"; // 인증 완료 축하메세지
            }
		}

        return json_encode($retval);
	}


}
