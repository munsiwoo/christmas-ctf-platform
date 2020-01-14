[![GitHub stars](https://img.shields.io/github/stars/munsiwoo/Christmas-CTF.svg)](https://github.com/munsiwoo/Christmas-CTF/stargazers)
[![GitHub license](https://img.shields.io/github/license/munsiwoo/Christmas-CTF.svg)](https://github.com/munsiwoo/Christmas-CTF/blob/master/LICENSE)

# Christmas-CTF
### What is this?

This is the platform I used for Christmas CTF 2019.  
I developed it in pure PHP and designed it with MVC pattern.  
It is a Jeopardy platform and uses Dynamic Scoring.  

> pseudo code (default, min_point=100 / max_point=1000)

```
point = (min_point+(max_point-min_point)/(1+(max(0,(solve_cnt)-1)/4.0467890)**3.84))
point = round(value)
```

> default account (when password salt is 255d943bf821b38d386935b775a01a21)

| username     | password     |
| ------------ | ------------ |
| admin        | admin        |
| test_captain | test_captain |
| test_member  | test_member  |

The platform is based on [@munsiwoo/simple-mvc-php](https://github.com/munsiwoo/simple-mvc-in-php).

### Picture

![main](https://i.imgur.com/1Ig5T5D.png)  

![prob](https://i.imgur.com/5VVoIWV.png)


## How to install?

I will be uploading Dockerfile soon..