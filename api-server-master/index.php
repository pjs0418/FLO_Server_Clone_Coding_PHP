<?php

require './pdos/DatabasePdo.php';
require './pdos/UserPdo.php';
require './pdos/TastePdo.php';
require './pdos/ChartPdo.php';
require './pdos/HomePdo.php';
require './pdos/PlayPdo.php';
require './pdos/ListPdo.php';
require './pdos/JWTPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   JWT   ****************** */
    $r->addRoute('POST', '/jwt', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
    $r->addRoute('GET', '/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사

    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['TasteController', 'index']);
    $r->addRoute('GET', '/users', ['IndexController', 'getUsers']);
    $r->addRoute('GET', '/users/{userIdx}', ['IndexController', 'getUserDetail']);
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']); // 비밀번호 해싱 예시 추가



    $r->addRoute('GET', '/overlap-users', ['UserController', 'checkOverlapUser']);
    $r->addRoute('POST', '/users', ['UserController', 'createUser']);
    $r->addRoute('POST', '/logins', ['UserController', 'userLogin']);
    $r->addRoute('PUT', '/users', ['UserController', 'deleteUser']);
    $r->addRoute('GET', '/logins', ['UserController', 'autoLogin']);
    $r->addRoute('POST', '/kakao-logins', ['UserController', 'userKakaoLogin']);
    $r->addRoute('POST', '/naver-logins', ['UserController', 'userNaverLogin']);

    $r->addRoute('GET', '/tastes', ['TasteController', 'getTastes']);
    $r->addRoute('PUT', '/tastes', ['TasteController', 'modifyTastes']);

    $r->addRoute('GET', '/charts', ['ChartController', 'getCharts']);
    $r->addRoute('GET', '/charts/mixs', ['ChartController', 'getMixedCharts']);

    $r->addRoute('GET', '/homes', ['HomeController', 'getHomes']);

    $r->addRoute('GET', '/songs/{songid}', ['PlayController', 'getPlayingSongs']);
    $r->addRoute('PUT', '/playlists', ['PlayController', 'savePlaylists']);

    $r->addRoute('POST', '/lists', ['ListController', 'createList']);
    $r->addRoute('POST', '/lists/{listid}/songs', ['ListController', 'addListSongs']);
    $r->addRoute('GET', '/lists', ['ListController', 'getLists']);
    $r->addRoute('PUT', '/lists', ['ListController', 'deleteLists']);
    $r->addRoute('GET', '/lists/{listid}', ['ListController', 'getListDetail']);
    $r->addRoute('PUT', '/lists/{listid}/songs', ['ListController', 'deleteListSongs']);


//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'JWTController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/JWTController.php';
                break;
            case 'UserController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/UserController.php';
                break;
            case 'TasteController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/TasteController.php';
                break;
            case 'ChartController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ChartController.php';
                break;
            case 'HomeController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/HomeController.php';
                break;
            case 'PlayController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/PlayController.php';
                break;
            case 'ListController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ListController.php';
                break;
        }

        break;
}
