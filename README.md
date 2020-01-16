[![GitHub stars](https://img.shields.io/github/stars/munsiwoo/christmas-ctf-platform.svg)](https://github.com/munsiwoo/christmas-ctf-platform/stargazers)
[![GitHub license](https://img.shields.io/github/license/munsiwoo/christmas-ctf-platform.svg)](https://github.com/munsiwoo/christmas-ctf-platform/blob/master/LICENSE)

# Christmas CTF Platform
### What is this?

This is the platform I used for Christmas CTF 2019. (challenges is [here](https://github.com/Aleph-Infinite/2019-Christmas-CTF).)  
I developed it in pure PHP and designed it with MVC pattern. (`Apache2` + `PHP7` + `MariaDB`)  
It is a Jeopardy style playform using Dynamic Scoring.  

> Dynamic Scoring pseudo code (default, min_point=100 / max_point=1000)

```
point = (min_point+(max_point-min_point)/(1+(max(0,(solve_cnt)-1)/4.0467890)**3.84))
point = round(value)
```

> Default account (when password salt is "255d943bf821b38d386935b775a01a21")
> Salt can be modified in /config/config.php

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

I will upload Dockerfile soon.
