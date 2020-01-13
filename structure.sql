SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+09:00"; /* Asia/Seoul (UTC+09:00) */
SET NAMES utf8mb4;

CREATE TABLE `mun_auth_logs` (
  `no` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `prob_name` varchar(100) NOT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `flag` varchar(100) DEFAULT NULL,
  `auth_date` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mun_config` (
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `max_point` int(11) NOT NULL,
  `min_point` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `mun_config` VALUES (now(), now(), 1000, 100);

CREATE TABLE `mun_notices` (
  `no` int(11) NOT NULL,
  `contents` text,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mun_probs` (
  `no` int(11) NOT NULL,
  `field` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `contents` varchar(5000) DEFAULT NULL,
  `flag` varchar(2000) DEFAULT NULL,
  `point` int(11) NOT NULL,
  `open` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mun_solves` (
  `no` int(11) NOT NULL,
  `prob_no` int(11) DEFAULT NULL,
  `teamname` varchar(100) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `auth_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mun_teams` (
  `teamname` varchar(100) NOT NULL,
  `invite_code` varchar(100) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `mun_teams` (`teamname`, `invite_code`) VALUES
('test', '22052c49d202c465f9b38363cac239a4fb68682bdab6f3729dbd5fdf686c0451'),
('admin', '51f3421a4e7e78fefa226a86c50248d501719fc60c1c53b16ac5268945799879');

CREATE TABLE `mun_users` (
  `usertype` varchar(100) NOT NULL,
  `teamname` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `last_auth` datetime DEFAULT NULL,
  `reg_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `mun_users` (`usertype`, `teamname`, `username`, `password`, `email`, `country`, `last_auth`, `reg_date`) VALUES
('captain', 'admin', 'admin', '8c37132e767e7577d9fe1018f4bcff37', 'mun.xiwoo@gmail.com', 'Korea, Republic of', '2020-01-13 08:44:18', '2020-01-13 08:44:18'),
('captain', 'test', 'test_captain', 'e702ead743a40df29cb2bb213c881c28', 'test_captain@test.com', 'Korea, Republic of', '2020-01-13 08:45:39', '2020-01-13 08:45:39'),
('member', 'test', 'test_member', '21857d911505b8703278b4e57e25b255', 'test_member@test.com', 'Korea, Republic of', '2020-01-13 08:45:39', '2020-01-13 08:46:04');

ALTER TABLE `mun_auth_logs`
  ADD PRIMARY KEY (`no`);

ALTER TABLE `mun_notices`
  ADD PRIMARY KEY (`no`);

ALTER TABLE `mun_probs`
  ADD PRIMARY KEY (`no`);

ALTER TABLE `mun_solves`
  ADD PRIMARY KEY (`no`);

ALTER TABLE `mun_teams`
  ADD PRIMARY KEY (`invite_code`);

ALTER TABLE `mun_auth_logs`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mun_notices`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mun_probs`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mun_solves`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;
