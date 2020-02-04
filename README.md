[![GitHub stars](https://img.shields.io/github/stars/munsiwoo/christmas-ctf-platform.svg)](https://github.com/munsiwoo/christmas-ctf-platform/stargazers)
[![GitHub license](https://img.shields.io/github/license/munsiwoo/christmas-ctf-platform.svg)](https://github.com/munsiwoo/christmas-ctf-platform/blob/master/LICENSE)

# Christmas CTF Platform
### What is this?

This is the platform I used for Christmas CTF 2019. (challenges is [here](https://github.com/Aleph-Infinite/2019-Christmas-CTF).)  
I developed it in pure PHP and designed it with MVC pattern. (`Apache2` + `PHP7` + `MariaDB`)  
It is a Jeopardy style platform using Dynamic Scoring.  

> Dynamic Scoring pseudo code (default, min_point=100 / max_point=1000)

```
point = (min_point+(max_point-min_point)/(1+(max(0,(solve_cnt)-1)/4.0467890)**3.84))
point = round(value)
```

> Default accounts  
> Salt can be modified in /src/config/config.php

| Username     | Password     |
| ------------ | ------------ |
| admin        | admin        |
| test_captain | test_captain |
| test_member  | test_member  |

The platform is based on [munsiwoo/simple-mvc-php](https://github.com/munsiwoo/simple-mvc-in-php).

### Preview images

![main](https://i.imgur.com/1Ig5T5D.png)  

![prob](https://i.imgur.com/5VVoIWV.png)

## How to install?

The `docker` and `docker-compose` must be installed first. (`apt install docker docker-compose`)

#### Step 1. Download the repository.

```bash
1. git clone https://github.com/munsiwoo/christmas-ctf-platform.git
2. cd christmas-ctf-platform
```

#### Step 2. Run the docker-compose

```bash
3. docker-compose build
4. docker-compose up -d
```

When the installation is complete, connect to `localhost:9999`   
The db connection information can be modified by modifying the file below.

* docker-compose.yml (MYSQL environments)
* /src/config/config.php (`__HOST__`, `__USER__`, `__PASS__`, `__NAME__`)

mysql default password is `root_password`