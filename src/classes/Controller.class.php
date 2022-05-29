<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Admin.class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/User.class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Ctf.class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Render.class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/MunTemplate.class.php';

class Controller {
    function __construct($http_method, $request_uri, $is_login, $is_admin) {
        $Ctf = new Ctf();
        $Admin = new Admin();
        $User = new User();
        $Render = new Render();
        $MunTemplate = new MunTemplate(__TEMPLATES__);
         
        /*
        // config/config.php에서 "__DOMAIN__" 설정해야 사용 가능

        if($http_method == 'POST') { // CSRF 방지
            $referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            if($referer !== __DOMAIN__) die('Not allowed referer!');
        }
        */

        /* header 부분 */
        if($http_method == 'GET' && !in_array($request_uri, __HIDDEN_HEADER__)) {
            $top_menu = $Render->get_top_menu($is_login, $is_admin);
            $MunTemplate->render_template('header.html', [
                'title' => __TITLE__,
                'top_menu' => $top_menu,
                'timestamp' => (int)(time() / 1000), // css, js 캐시 비울 때
            ]);
        }

        switch($request_uri) {
            case '/' :
                /*
                if(!isset($_COOKIE['sponsor'])) {
                    setcookie('sponsor', 'foo', time() + 7200);
                    header('Location: /sponsor');
                }
                */
                $MunTemplate->render_template('index.html');
                break;

            case '/sponsor' :
                $MunTemplate->render_template('sponsor.html');
                break;

            case '/logout' :
                session_destroy();
                redirect_url('/');
                break;

            case '/login' :
                if($is_login) redirect_url('/');
                if($http_method == 'POST') {
                    echo $User->login($_POST);
                    break;
                }
                $MunTemplate->render_template('login.html');
                break;

            case '/register' :
                if($is_login) redirect_url('/');
                $MunTemplate->render_template('register.html');
                break;

            case '/register/captain' :
                if($is_login) redirect_url('/');
                if($http_method == 'POST') {
                    echo $User->register($_POST);
                    break;
                }
                $MunTemplate->render_template('register_captain.html');
                break;

            case '/register/member' :
                if($is_login) redirect_url('/');
                if($http_method == 'POST') {
                    echo $User->register($_POST);
                    break;
                }
                $MunTemplate->render_template('register_member.html');
                break;

            case '/mypage' :
                if(!$is_login) redirect_url('/login', 'Please login!');

                $user_info = array_map('htmlspecialchars', $Render->get_mypage($_SESSION['username']));
                $solved_probs = $Render->get_solved_probs_for_mypage($_SESSION['username']);
                $invite_code = $Render->get_invite_code($_SESSION['teamname']);
                $captain = $Render->get_captain_of_team($_SESSION['teamname']);

                $MunTemplate->render_template('mypage.html',
                    [
                        'user_info' => $user_info, 
                        'user_point' => $user_point,
                        'solved_probs' => $solved_probs,
                        'invite_code' => $invite_code,
                        'captain' => $captain,
                    ]
                );
                break;

            case '/rank' :
                $rank_list = $Render->get_rank_list();
                $MunTemplate->render_template('rank.html', ['rank_list' => $rank_list]);
                break;

            case '/render/rank' :
                if($http_method == 'POST') {
                    $rank_list = $Render->get_rank_list();
                    $MunTemplate->render_template('render_rank.html', ['rank_list' => $rank_list]);
                    break;
                }
                redirect_url('/');
                break;

            case '/prob' :
                if(!$is_login) redirect_url('/login', 'Please login!');
                $categories = $Render->get_prob_categories();
                $prob_list = $Render->get_prob_list($_GET['category']);

                $MunTemplate->render_template('prob.html',
                    [
                        'categories' => $categories,
                        'prob_list' => $prob_list,
                    ]
                );
                break;

            case '/check_flag' :
                if(!$is_login) redirect_url('/login', 'Please login!');
                if($http_method == 'POST') {
                    echo $Ctf->check_flag($_POST['no'], $_POST['flag'], $_SESSION['username']);
                }
                break;

            case '/notice' :
                $notice_list = $Render->get_notice_list();
                $MunTemplate->render_template('notice.html', ['notice_list' => $notice_list]);
                break;

            case '/change_password' :
                if(!$is_login) redirect_url('/login', 'Please login!');
                if($http_method == 'POST') {
                    echo $User->change_password($_POST, $_SESSION['username']);
                    break;
                }
                $MunTemplate->render_template('change_password.html');
                break;

            case '/team_info' :
                if(!$_GET['team']) redirect_url('/', 'Invalid team name');
                $team_info = $Render->get_team_info($_GET['team']);
                
                if($team_info) $MunTemplate->render_template('team.html', ['team_info' => $team_info]);
                else redirect_url('/', 'Invalid team name');
                break;

            case '/solved_teams' :
                if(!$is_login) redirect_url('/login', 'Please login!');
                if($http_method == 'POST') {
                    if(!$_POST['prob_no']) redirect_url('/', 'Invalid prob no');
                    $solved_teams = $Render->get_solved_teams($_POST['prob_no']);
                    echo json_encode($solved_teams);
                    break;
                }
                redirect_url('/', 'Invalid method');
                break;

            case '/get_countdown' :
                $config = $Admin->get_config();
                echo str_replace(' ', 'T', $config['start_time']);
                break;

            // 여기서부터 어드민 페이지
            case '/admin' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                $MunTemplate->render_template('admin/admin.html');
                break;

            case '/admin/manage_prob' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                if($http_method == 'POST') {
                    if($_POST['mode'] == 'add')
                        echo $Admin->add_prob($_POST);
                    else if($_POST['mode'] == 'edit')
                        echo $Admin->edit_prob($_POST);
                    else if($_POST['mode'] == 'delete')
                        echo $Admin->delete_prob($_POST);
                    break;
                }
                $prob_list = $Admin->get_all_prob_list();
                $MunTemplate->render_template('admin/manage_prob.html', ['prob_list' => $prob_list]);
                break;

            case '/admin/manage_notice' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                if($http_method == 'POST') {
                    if($_POST['mode'] == 'add')
                        echo $Admin->add_notice($_POST);
                    else if($_POST['mode'] == 'edit')
                        echo $Admin->edit_notice($_POST);
                    else if($_POST['mode'] == 'delete')
                        echo $Admin->delete_notice($_POST);
                    break;
                }
                $notice_list = $Render->get_notice_list();
                $MunTemplate->render_template('admin/manage_notice.html', ['notice_list' => $notice_list]);
                break;

            case '/admin/auth_log' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                $auth_logs = $Admin->get_auth_logs();
                $MunTemplate->render_template('admin/auth_log.html', ['auth_logs' => $auth_logs]);
                break;

            case '/render/auth_log' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                if($http_method == 'POST') {
                    $auth_logs = $Admin->get_auth_logs();
                    $MunTemplate->render_template('admin/render_auth_log.html', ['auth_logs' => $auth_logs]);
                    break;
                }
                redirect_url('/');
                break;

            case '/admin/view_solver' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                $solvers = $Admin->get_solvers();
                $MunTemplate->render_template('admin/view_solver.html', ['solvers' => $solvers]);
                break;

            case '/admin/all_prob' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                if($http_method == 'POST') {
                    $Admin->manage_all_prob($_POST['mode']);
                    break;
                }
                break;

            case '/admin/reset_password' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                if($http_method == 'POST') {
                    if($_POST['username'] === __ADMIN__) { // 어드민은 비번 랜덤 리셋 불가
                        echo json_encode(['status' => false, 'message' => 'melong']);
                        break;
                    }
                    echo $Admin->reset_password($_POST['username']);
                    break;
                }
                $MunTemplate->render_template('admin/reset_password.html');
                break;

            case '/admin/delete_team' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                if($http_method == 'POST') {
                    if($_POST['teamname'] === $_SESSION['teamname']) { // 어드민 팀은 삭제 불가
                        echo json_encode(['status' => false, 'message' => 'melong']);
                        break;
                    }
                    echo $Admin->delete_team($_POST['teamname']);
                    break;
                }
                $MunTemplate->render_template('admin/delete_team.html');
                break;

            case '/admin/user_list' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                $user_list = $Admin->get_user_list();
                $MunTemplate->render_template('admin/user_list.html',
                    [
                        'user_list' => $user_list,
                        'user_cnt' => count($user_list)
                    ]
                );
                break;

            case '/admin/edit_config' :
                if(!$is_admin) redirect_url('/', 'You are not admin!');
                if($http_method == 'POST') {
                    echo $Admin->edit_config($_POST);
                    break;
                }
                $config = $Admin->get_config();
                
                $start_time = datetime_to_array($config['start_time']);
                $end_time = datetime_to_array($config['end_time']);
                $max_point = $config['max_point'];
                $min_point = $config['min_point'];

                $MunTemplate->render_template('admin/edit_config.html', [
                    'start_date' => "{$start_time['y']}-{$start_time['m']}-{$start_time['d']}",
                    'start_time' => "{$start_time['h']}:{$start_time['i']}:{$start_time['s']}",
                    'end_date' => "{$end_time['y']}-{$end_time['m']}-{$end_time['d']}",
                    'end_time' => "{$end_time['h']}:{$end_time['i']}:{$end_time['s']}",
                    'max_point' => $max_point,
                    'min_point' => $min_point,
                ]);
                break;

            case '/robots.txt' :
                header('Content-Type: text/plain; charset=UTF-8');
                $MunTemplate->render_template('robots.txt');
                break;

            default :
                header("HTTP/1.1 404 Not Found");
                $MunTemplate->render_template('404.html');
    
        }

        /* footer 부분 */
        if($http_method == 'GET' && !in_array($request_uri, __HIDDEN_HEADER__)) {
            $MunTemplate->render_template('footer.html');
        }
    }
}
