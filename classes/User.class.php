<?php
class User extends mysqli {
	function __construct() {
		parent::__construct(__HOST__, __USER__, __PASS__, __NAME__);
	}

    private function generate_invite_code() { // 초대 코드 생성
        return bin2hex(random_bytes(32));
    }

	function login($data) {
		$username = addslashes($data['username']);
		$password = process_password($data['password']);
		
		$query = "SELECT * FROM mun_users WHERE BINARY username='{$username}' AND password='{$password}'";
		$result = $this->query($query);
		$fetch = $result->fetch_array(MYSQLI_ASSOC);
        $retval = ['status' => false];

		if($fetch['username']) {
			if($fetch['username'] === __ADMIN__) {
				$_SESSION['admin'] = true;
			}
			$_SESSION['username'] = $fetch['username'];
            $_SESSION['teamname'] = $fetch['teamname'];
			$retval['status'] = true;
		}

        return json_encode($retval);
	}

	function register($data) {
		$usertype = addslashes($data['usertype']);
		$username = addslashes(trim(mb_substr($data['username'], 0, 50)));
		$password = process_password($data['password']);
        $email = addslashes(trim(mb_substr($data['email'], 0, 50)));
        $country = addslashes($data['country']);
        $retval = ['status' => false];

        // 비번 길이, 이메일 형식, 나라, 유저타입 체크
        if(mb_strlen($data['password']) < 5) {
            $retval['message'] = "Your password is too short.";
            return json_encode($retval);
        } 
        if(!in_array($data['usertype'], __USER_TYPE__)) {
            $retval['message'] = "Invalid usertype!";
            return json_encode($retval);
        }
        if(!in_array($data['country'], __COUNTRY__)) {
            $retval['message'] = "Invalid country!";
            return json_encode($retval);
        }
        if(!preg_match("/^.+\@.+[.].+$/is", $data['email'])) {
            $retval['message'] = "Invalid email!";
            return json_encode($retval);
        }

        // 유저네임 중복 체크
		$result = $this->query("SELECT * FROM mun_users WHERE BINARY username='{$username}'");
		if($result->fetch_array(MYSQLI_ASSOC)) {
            $retval['message'] = "Already exists user name!";
            return json_encode($retval);
        }
        // 이메일 중복 체크
        $result = $this->query("SELECT * FROM mun_users WHERE email='{$email}'");
        if($result->fetch_array(MYSQLI_ASSOC)) {
            $retval['message'] = "Already exists email!";
            return json_encode($retval);
        }

        if($usertype === 'captain') { // 팀장 가입
            if(!trim($data['teamname']) || mb_strlen($data['teamname']) > 70) { // 팀명이 비어있거나 70자 넘는지 확인
                $retval['message'] = "Invalid team name!";
                return json_encode($retval);
            }
            
            $teamname = addslashes(trim($data['teamname']));
            $result = $this->query("SELECT * FROM mun_teams WHERE BINARY teamname='{$teamname}'"); // 팀명 중복 체크
            if($result->fetch_array(MYSQLI_ASSOC)) {
                $retval['message'] = "Already exists team name!";
                return json_encode($retval);
            }

            $invite_code = $this->generate_invite_code(); // 팀 가입 코드 생성
            $retval['status'] = true;
            
            if(!$this->query("INSERT INTO mun_users VALUES ('{$usertype}', '{$teamname}', '{$username}', '{$password}', '{$email}', '{$country}', now(), now())")) {
                $retval['status'] = false;
                $retval['message'] = "Failed.";
                return json_encode($retval);
            }
            if(!$this->query("INSERT INTO mun_teams VALUES ('{$teamname}', '{$invite_code}')")) {
                $retval['status'] = false;
                $retval['message'] = "Failed.";
                return json_encode($retval);
            }
        }

        else if($usertype === 'member') { // 팀원 가입
            $invite_code = addslashes($data['invite_code']);
            $result = $this->query("SELECT * FROM mun_teams WHERE BINARY invite_code='{$invite_code}'");
            
            if($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
                $teamname = addslashes($fetch['teamname']);
                $retval['status'] = true;

                $last_auth = $this->query("SELECT max(last_auth) FROM mun_users WHERE BINARY teamname='{$teamname}'");
                $last_auth = $last_auth->fetch_array(MYSQLI_NUM)[0]; // 팀에서 가장 최근에 인증한 사람 시간으로 가져옴
                if(!$this->query("INSERT INTO mun_users VALUES ('{$usertype}', '{$teamname}', '{$username}', '{$password}', '{$email}', '{$country}', '{$last_auth}', now())")) {
                    $retval['message'] = "Failed.";
                    $retval['status'] = false;
                }
            }
            else {
                $retval['message'] = "Invalid invite code!";
            }
        }

		return json_encode($retval);
	}

    function change_password($data, $username) {
        $current_password = process_password($data['current_password']);
        $new_password = process_password($data['new_password']);
        $username = addslashes($username);

        $result = $this->query("SELECT password FROM mun_users WHERE BINARY username='{$username}' AND password='{$current_password}'");
        $fetch = $result->fetch_array(MYSQLI_ASSOC);
        $retval = ['status'=>false, 'message'=>'The current password is not valid.'];

        if($fetch) {
            if(mb_strlen($data['new_password']) < 5) {
                $retval['message'] = "Your password is too short.";
                return json_encode($retval);
            }

            $this->query("UPDATE mun_users SET password='{$new_password}' WHERE BINARY username='{$username}'");
            $retval['status'] = true;
            $retval['message'] = 'Password change successful!';
        }

        return json_encode($retval);
    }
    
}
