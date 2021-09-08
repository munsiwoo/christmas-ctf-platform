[![GitHub stars](https://img.shields.io/github/stars/munsiwoo/christmas-ctf-platform.svg)](https://github.com/munsiwoo/christmas-ctf-platform/stargazers)
[![GitHub license](https://img.shields.io/github/license/munsiwoo/christmas-ctf-platform.svg)](https://github.com/munsiwoo/christmas-ctf-platform/blob/master/LICENSE)

# Christmas CTF Platform
### What is this?

This is a CTF platform that I used for 2019 Christmas CTF. (you can see challenges, [here](https://github.com/Aleph-Infinite/2019-Christmas-CTF))  
It is implemented in pure PHP and designed with the MVC pattern.  
I chosen Jeopardy style and dynamic scoring method.  
  
(This platform is based on [munsiwoo/simple-mvc-php](https://github.com/munsiwoo/simple-mvc-in-php))
  
> Dynamic scoring calculation formula (min_point=100 / max_point=1000)

```
round(min_point+(max_point-min_point)/(1+(max(0,(solve_cnt)-1)/4.0467890)**3.84))
```


> You can change the hash salt in /src/config/config.php  
> The account below is default account for testing.  

| Username     | Password     |
| ------------ | ------------ |
| admin        | admin        |
| test_captain | test_captain |
| test_member  | test_member  |


### Preview images

![main](https://i.imgur.com/1Ig5T5D.png)  

![prob](https://i.imgur.com/5VVoIWV.png)

## How to install

First, you need to install `docker` and `docker-compose` (`apt install docker docker-compose`)

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

When the installation is complete, you can connect to `http://localhost:9999`   
If you wanna change the db connection information, please modify the following two files.

* docker-compose.yml
  * `MYSQL environments`
* src/config/config.php
  * `__HOST__, __USER__, __PASS__, __NAME__`

Mysql default password is `root_password`
