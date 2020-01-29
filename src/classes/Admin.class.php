<?php
class Admin extends mysqli {
    function __construct() {
        parent::__construct(__HOST__, __USER__, __PASS__, __NAME__);
    }

    private function is_flag($flag, $prob_name) { // 인증 로그에서 맞는 플래그인지 구분해줄 때 사용하는 함수
        $flag = addslashes(htmlspecialchars_decode($flag));
        $prob_name = addslashes(htmlspecialchars_decode($prob_name));
        $result = $this->query("SELECT flag FROM mun_probs WHERE BINARY flag='{$flag}' AND name='{$prob_name}'");

        if($result->fetch_array(MYSQLI_NUM)) {
            return true;
        }
        return false;
    }

    private function get_teamname($username) { // 유저 이름으로 팀명 가져오는 함수
        $username = addslashes(htmlspecialchars_decode($username));
        $result = $this->query("SELECT teamname FROM mun_users WHERE BINARY username='{$username}'");
        $teamname = $result->fetch_array(MYSQLI_NUM)[0];
        return $teamname;
    }

    private function get_solve_cnt($prob_no) { // 문제 번호로 해결한 팀 수 가져오는 함수
        $prob_no = (int)$prob_no;
        $result = $this->query("SELECT count(*) FROM mun_solves WHERE prob_no='{$prob_no}'");
        $solve_cnt = (int)$result->fetch_array(MYSQLI_NUM)[0];

        return $solve_cnt;
    }

    private function get_dynamic_point($prob_no) { // 문제 번호로 해당 문제 점수 계산하는 함수
        $config = $this->get_config();
        $max_point = (int)$config['max_point'];
        $min_point = (int)$config['min_point'];

        $solve_cnt = $this->get_solve_cnt($prob_no);
        $retval = [];

        $prob_point = (int)round($min_point + ($max_point - $min_point) / (1 + (max(0, ($solve_cnt) - 1) / 4.0467890) ** 3.84));
        $prob_point = max($prob_point, $min_point);

        return $prob_point;
    }

    private function update_point_all_prob() { // max_point 변경하면 모든 문제에 적용되도록 하는 함수
        $result = $this->query("SELECT no FROM mun_probs");

        while($fetch = $result->fetch_array(MYSQLI_NUM)) {
            $new_point = $this->get_dynamic_point($fetch[0]);
            $this->query("UPDATE mun_probs SET point='{$new_point}' WHERE no='{$fetch[0]}'");
        }
        return true;
    }
    
    function get_config() {
        $result = $this->query('SELECT * FROM mun_config');
        $config = $result->fetch_array(MYSQLI_ASSOC);
        return $config;
    }

    function get_all_prob_list() { // 모든 문제 출력하는 함수
        $result = $this->query("SELECT * FROM mun_probs ORDER BY no");
        $retval = [];

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            array_push($retval, [
                "no" => $fetch['no'],
                "name" => htmlspecialchars($fetch['name']),
                "field" => htmlspecialchars($fetch['field']),
                "contents" => htmlspecialchars($fetch['contents']),
                "open" => (int)$fetch['open'],
            ]);
        }
        return $retval;
    }

    function get_auth_logs() { // 인증 로그 최근 100개 가져오는 함수
        $result = $this->query("SELECT * FROM mun_auth_logs ORDER BY no DESC limit 0, 100");
        $retval = [];

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            $fetch = array_map('htmlspecialchars', $fetch);
            array_push($retval, [
                'username' => $fetch['username'],
                'teamname' => htmlspecialchars($this->get_teamname($fetch['username'])),
                'prob_name' => $fetch['prob_name'],
                'ip' => $fetch['ip'],
                'flag' => $fetch['flag'],
                'auth_date' => $fetch['auth_date'],
                'is_flag' => $this->is_flag($fetch['flag'], $fetch['prob_name']),
            ]);
        }
        return $retval;
    }

    function add_prob($data) {
        $data = array_map('addslashes', $data);
        $config = $this->get_config();
        $retval = ['result' => false];
    
        $field = $data['field'];
        $flag = $data['flag'];
        $name = $data['name'];
        $contents = $data['contents'];
        $point = (int)$config['max_point'];
        $open = 0;

        if($this->query("INSERT INTO mun_probs VALUES (NULL, '{$field}', '{$name}', '{$contents}', '{$flag}', '{$config['max_point']}', '{$open}')")) {
            $retval['result'] = true;
        }
        
        return json_encode($retval);
    }

    function edit_prob($data) {
        $data = array_map('addslashes', $data);

        $no = (int)$data['prob_no'];
        $field = $data['field'];
        $flag = $data['flag'];
        $name = $data['name'];
        $open = $data['open'] ? 1 : 0;
        $contents = $data['contents'];
        
        $update_columns = "field='{$field}', name='{$name}', contents='{$contents}', open='{$open}'";

        if(strlen($flag)) {
            $update_columns .= ", flag='{$flag}'";
        }

        $retval = ['result' => false];
        if($this->query("UPDATE mun_probs SET {$update_columns} WHERE no='{$no}'")) {
            $retval['result'] = true;
        }

        return json_encode($retval);
    }

    function delete_prob($data) {
        $prob_no = (int)$data['prob_no'];
        $this->query("DELETE FROM mun_probs WHERE no='{$prob_no}'");
        $this->query("DELETE FROM mun_solves WHERE prob_no='{$prob_no}'");

        return json_encode(['result' => true]);
    }

    function add_notice($data) {
        $data = array_map('addslashes', $data);
        $retval = ['result' => false];
        if($this->query("INSERT INTO mun_notices VALUES (NULL, '{$data['contents']}', now())"))
            $retval['result'] = true;
        return json_encode($retval);
    }

    function edit_notice($data) {
        $data = array_map('addslashes', $data);
        $data['no'] = (int)$data['no'];
        $this->query("UPDATE mun_notices SET contents='{$data['contents']}' WHERE no='{$data['no']}'");
        return json_encode(['result' => true]);
    }

    function delete_notice($data) {
        $data['no'] = (int)$data['no'];
        $this->query("DELETE FROM mun_notices WHERE no='{$data['no']}'");
        return json_encode(['result' => true]);
    }

    function get_solvers() { // 전체 문제에 대한 솔버 표시해주는 함수
        $result = $this->query("SELECT mun_probs.no as prob_no, mun_probs.name as prob_name, mun_solves.username as username FROM mun_probs LEFT JOIN mun_solves ON mun_probs.no=mun_solves.prob_no");
        $retval = [];
        $append_control = true;

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            $fetch = array_map('htmlspecialchars', $fetch);
            foreach($retval as $index=>$prob) {
                if($prob['prob_no'] == $fetch['prob_no']) {
                    $retval[$index]['username'] .= ",{$fetch['username']}";
                    $append_control = false;
                }
            }
            if($append_control) {
                array_push($retval, [
                    "prob_no" => $fetch['prob_no'],
                    "prob_name" => $fetch['prob_name'],
                    "username" => $fetch['username'],
                ]);
            }
            $append_control = true;
        }
        return $retval;
    }

    function manage_all_prob($mode) {
        $open = $mode == 'open' ? 1 : 0;
        $this->query("UPDATE mun_probs SET open='{$open}'");
        return true;
    }

    function reset_password($username) {
        $username = addslashes($username);
        $new_password = substr(md5(random_bytes(20)),1,7);
        $hash_password = process_password($new_password);
        $retval = ['status' => false, 'message' => 'Failed.'];

        $result = $this->query("SELECT * FROM mun_users WHERE BINARY username='{$username}'");
        if(!$result->fetch_array(MYSQLI_ASSOC)) {
            $retval['message'] = 'The username does not exist.';
            return json_encode($retval);
        }

        if($this->query("UPDATE mun_users SET password='{$hash_password}' WHERE BINARY username='{$username}'")) {
            $retval['status'] = true;
            $retval['message'] = 'Success.';
            $retval['new_password'] = $new_password;
        }

        return json_encode($retval);
    }

    function get_user_list() {
        $result = $this->query("SELECT * FROM mun_users");
        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            $fetch = array_map('htmlspecialchars', $fetch);
            $retval[] = $fetch;
        }
        return $retval;
    }

    function delete_team($teamname) { // 팀명으로 팀 삭제하는 함수
        $teamname = addslashes($teamname);
        $retval = ['status' => false];

        $result = $this->query("SELECT * FROM mun_users WHERE BINARY teamname='{$teamname}'");
        if(!$result->fetch_array(MYSQLI_NUM)) {
            $retval['message'] = 'This team name does not exist.';
            return json_encode($retval);
        }

        if(!$this->query("DELETE FROM mun_users WHERE BINARY teamname='{$teamname}'")) { // mun_users 테이블에서 삭제
            $retval['message'] = 'Failed to delete from the mun_users table.';
            return json_encode($retval);
        }
        if(!$this->query("DELETE FROM mun_teams WHERE BINARY teamname='{$teamname}'")) { // mun_teams 테이블에서 삭제
            $retval['message'] = 'Failed to delete from the mun_teams table.';
            return json_encode($retval);
        }
        if(!$this->query("DELETE FROM mun_solves WHERE BINARY teamname='{$teamname}'")) { // mun_solves 테이블에서 삭제
            $retval['message'] = 'Failed to delete from the mun_solves table.';
            return json_encode($retval);
        }

        $retval['status'] = true;
        return json_encode($retval);
    }

    function edit_config($data) {
        $data = array_map('addslashes', $data);
        $start_time = $data['start_date'].' '.$data['start_time'];
        $end_time = $data['end_date'].' '.$data['end_time'];
        $max_point = (int)$data['max_point'];
        $min_point = (int)$data['min_point'];
        $retval = ['status' => false, 'message' => 'Failed!'];

        if(!preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}(?::\d{2})?/', $start_time)
        or !preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}(?::\d{2})?/', $end_time)) {
            $retval['message'] = 'Invalid time format!';
            return json_encode($retval);
        }

        if($min_point >= $max_point) {
            $retval['message'] = 'Maximum must be greater than minimum!';
            return json_encode($retval);
        }

        if($this->query("UPDATE mun_config SET start_time='{$start_time}', end_time='{$end_time}', max_point='{$max_point}', min_point='{$min_point}' WHERE 1")) {
            $this->update_point_all_prob(); // 모든 문제 포인트 업데이트
            $retval['status'] = true;
            $retval['message'] = 'Saved.';
        }

        return json_encode($retval);
    }

}
