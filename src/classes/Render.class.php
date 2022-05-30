<?php
class Render extends mysqli {
    function __construct() {
        parent::__construct(__HOST__, __USER__, __PASS__, __NAME__);
    }

    private function get_solved_probs_from_team($teamname) { // 팀이름으로 푼 문제 가져오는 함수
        $teamname = addslashes($teamname);
        $result = $this->query("SELECT * FROM mun_solves WHERE BINARY teamname='{$teamname}'");
        $solved_probs = [];

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            array_push($solved_probs, $fetch['prob_no']);
        }

        return $solved_probs;
    }

    private function get_solver_cnt($prob_no) { // 문제 솔버수 가져오는 함수
        $prob_no = (int)$prob_no;
        $result = $this->query("SELECT count(*) as cnt FROM mun_solves WHERE prob_no='{$prob_no}'");
        $fetch = $result->fetch_array(MYSQLI_ASSOC);
        $solver_cnt = (int)$fetch['cnt'];

        return $solver_cnt;
    }

    private function get_first_solver($prob_no) { // 퍼블 팀명 가져오는 함수
        $prob_no = (int)$prob_no;
        $result = $this->query("SELECT teamname FROM mun_solves WHERE prob_no='{$prob_no}' ORDER BY no");
        $fetch = $result->fetch_array(MYSQLI_ASSOC);
        $retval = 'Not solve yet';

        if($fetch) {
            $first_solver = htmlspecialchars($fetch['teamname']);
            $retval = '🏅'.$first_solver;
        }

        return $retval;
    }

    private function get_prob_name($prob_no) { // 문제 번호로 문제 이름 가져오는 함수
        $prob_no = (int)$prob_no;
        $result = $this->query("SELECT name FROM mun_probs WHERE no='{$prob_no}'");
        $prob_name = $result->fetch_array(MYSQLI_ASSOC)['name'];
        return $prob_name;
    }

    function get_prob_list($category) { // 오픈된 문제 가져오는 함수
        $allow_categories = $this->get_prob_categories();
        $where = 'open=1';

        if(in_array($category, $allow_categories)) { // 존재하는 카테고리면 해당 카테고리 문제만 가져오도록 where 조건추가
            $category = addslashes($category);
            $where .= " AND field='{$category}'";
        }

        $result = $this->query("SELECT * FROM mun_probs WHERE {$where} ORDER BY no");
        $solved_probs = $this->get_solved_probs_from_team($_SESSION['teamname']); // 팀명으로 푼 문제 가져옴
        $retval = [];

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            $solved = in_array($fetch['no'], $solved_probs) ? 1 : 0; // 인증된 문제 or 안된 문제
            $solver_cnt = $this->get_solver_cnt($fetch['no']);
            $first_solver = $this->get_first_solver($fetch['no']);

            array_push($retval, [
                'no' => $fetch['no'],
                'field' => htmlspecialchars($fetch['field']),
                'name' => htmlspecialchars($fetch['name']),
                'point' => $fetch['point'],
                'contents' => $fetch['contents'], // 문제 본문에서는 html 사용 가능
                'solver_cnt' => $solver_cnt,
                'first_solver' => $first_solver, // get_first_solver함수에서 htmlspecialchars 처리함
                'solved' => $solved,
            ]);
        }

        return $retval;
    }

    function get_rank_list() { // 다이나믹 랭킹 표시
        $admin_username = addslashes(__ADMIN__);
        $result = $this->query("SELECT u.teamname teamname, ifnull(sum(p.point), 0) point, ifnull(max(s.auth_date), min(u.reg_date)) last_auth FROM mun_users u LEFT OUTER JOIN mun_solves s ON BINARY u.username=s.username LEFT OUTER JOIN mun_probs p ON s.prob_no=p.no WHERE BINARY u.username!='{$admin_username}' GROUP BY BINARY u.teamname ORDER BY point DESC, last_auth");
        // ifnull(max(s.auth_date), min(u.reg_date)) -> 푼 문제가 있으면 가장 최근 인증시간 가져오고 없으면 가입 날짜
        $retval = [];
        $place = 1;

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            array_push($retval, [
                'place' => $place++,
                'teamname' => htmlspecialchars($fetch['teamname']),
                'point' => $fetch['point'],
                'last_auth' => $fetch['last_auth'],
            ]);
        }
        return $retval;
    }

    function get_solved_probs_for_mypage($username) { // 마이페이지에 표시할 내가 인증한 문제
        $username = addslashes($username);
        $result = $this->query("SELECT * FROM mun_solves WHERE BINARY username='{$username}'");
        $solved_probs = [];

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            $get_prob_name = $this->query("SELECT name FROM mun_probs WHERE no='{$fetch['prob_no']}'");
            $prob_name = $get_prob_name->fetch_array(MYSQLI_ASSOC)['name'];

            array_push($solved_probs, [
                $fetch['prob_no'],
                htmlspecialchars($prob_name),
                $fetch['auth_date'],
            ]);
        }
        return $solved_probs;
    }

    function get_notice_list() {
        $result = $this->query("SELECT * FROM mun_notices ORDER BY no DESC");
        $retval = [];

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            array_push($retval, [
                'no' => $fetch['no'],
                'contents' => $fetch['contents'],
                'date' => $fetch['date'],
            ]);
        }
        return $retval;
    }

    function get_prob_categories() { // 문제 분야 가져오는 함수
        $result = $this->query("SELECT distinct field FROM mun_probs ORDER BY field");
        $retval = [];

        while($fetch = $result->fetch_array(MYSQLI_ASSOC))
            array_push($retval, $fetch['field']);

        return $retval;
    }

    function get_mypage($username) { // 마이페이지에 표시할 유저 정보
        $username = addslashes($username);
        $result = $this->query("SELECT * FROM mun_users WHERE BINARY username='{$username}'");
        $fetch = $result->fetch_array(MYSQLI_ASSOC);
        return $fetch;
    }

    function get_invite_code($teamname) { // 마이페이지에서 초대 코드 표시
        $teamname = addslashes($teamname);
        $result = $this->query("SELECT * FROM mun_teams WHERE BINARY teamname='{$teamname}'");
        $fetch = $result->fetch_array(MYSQLI_ASSOC);

        return $fetch['invite_code'];
    }

    function get_captain_of_team($teamname) { // 마이페이지에서 팀장 표시
        $teamname = addslashes($teamname);
        $result = $this->query("SELECT username FROM mun_users WHERE BINARY teamname='{$teamname}' AND usertype='captain'");
        $fetch = $result->fetch_array(MYSQLI_ASSOC);

        return htmlspecialchars($fetch['username']);
    }

    function get_team_info($teamname) { // 팀 정보 가져오는 함수 (/team_info)
        $teamname = addslashes($teamname);
        $get_members = $this->query("SELECT username, usertype FROM mun_users WHERE BINARY teamname='{$teamname}'");
        $get_point = $this->query("SELECT ifnull(sum(p.point), 0) FROM mun_users u LEFT OUTER JOIN mun_solves s ON BINARY u.username=s.username LEFT OUTER JOIN mun_probs p ON s.prob_no=p.no WHERE BINARY u.teamname='{$teamname}'");
        $get_solved_probs = $this->query("SELECT * FROM mun_solves WHERE BINARY teamname='{$teamname}'");
        $retval = ['teamname' => htmlspecialchars($teamname), 'point' => $get_point->fetch_array(MYSQLI_NUM)[0]];

        while($member = $get_members->fetch_array(MYSQLI_ASSOC)) {
            $is_team = true;
            $usertype = ucfirst(htmlspecialchars($member['usertype']));
            $username = htmlspecialchars($member['username']);
            $retval['members'][] = [$usertype, $username];
        }

        while($solved_probs = $get_solved_probs->fetch_array(MYSQLI_ASSOC)) {
            $prob_name = htmlspecialchars($this->get_prob_name($solved_probs['prob_no']));
            $username = htmlspecialchars($solved_probs['username']);
            $auth_date = $solved_probs['auth_date'];
            $retval['solved_probs'][] = [$prob_name, $username, $auth_date];
        }

        if(!$is_team) return false; // 존재하지 않는팀이면 false
        return $retval;
    }

    function get_solved_teams($prob_no) { // 특정 문제에 대한 상위 10팀 솔버 출력
        $prob_no = (int)$prob_no;
        $result = $this->query("SELECT teamname FROM mun_solves WHERE prob_no='{$prob_no}' ORDER BY auth_date LIMIT 0, 10");
        $retval = ['status' => false];
        $index = 1;

        while($fetch = $result->fetch_array(MYSQLI_ASSOC)) {
            $retval['teams'][] = $index.' : '.htmlspecialchars($fetch['teamname']);
            $index += 1;
        }

        if(isset($retval['teams'])) $retval['status'] = true;
        return $retval;
    }
}
