upstream splash {
  {{range service "splash-web-nginx"}}server {{.Address}}:{{.Port}} max_fails=3 fail_timeout=60 weight=1;
  {{else}}server 127.0.0.1:65535;{{end}}
}

upstream cms {
  {{range service "cms-web-ghost"}}server {{.Address}}:{{.Port}} max_fails=3 fail_timeout=60 weight=1;
  {{else}}server 127.0.0.1:65535;{{end}}
}

upstream docAgent {
  {{range service "docAgent-api-listen"}}{{ if (ne .Port 0) }}server {{.Address}}:{{.Port}} max_fails=3 fail_timeout=60 weight=1;
  {{end}}{{else}}server 127.0.0.1:65535;{{end}}
}

upstream mailer {
  {{range service "mailer-api-listen"}}{{ if (ne .Port 0) }}server {{.Address}}:{{.Port}} max_fails=3 fail_timeout=60 weight=1;
  {{end}}{{else}}server 127.0.0.1:65535;{{end}}
}

upstream goapi {
  {{range service "golang-api-listen"}}{{ if (ne .Port 0) }}server {{.Address}}:{{.Port}} max_fails=3 fail_timeout=60 weight=1;
  {{end}}{{else}}server 127.0.0.1:65535;{{end}}
}

server {
  listen 80 default_server;
  server_name localhost;

  location / {

    location /simplegoapi {
      proxy_set_header Host $host;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
      proxy_set_header X-Forwarded-Proto $scheme;

      rewrite ^/simplegoapi/?(.*) /$1 break;
      proxy_pass http://goapi;
      proxy_redirect off;
    }

    location /splash {
      proxy_set_header Host $host;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
      proxy_set_header X-Forwarded-Proto $scheme;

      rewrite ^/splash/?(.*) /$1 break;
      proxy_pass http://splash;
      proxy_redirect off;
    }

    location /docagent {
      proxy_set_header Host $host;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
      proxy_set_header X-Forwarded-Proto $scheme;

      rewrite ^/docagent/?(.*) /$1 break;
      proxy_pass http://docAgent;
      proxy_redirect off;
    }

    location /mailer {
      proxy_set_header Host $host;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
      proxy_set_header X-Forwarded-Proto $scheme;

      rewrite ^/mailer/?(.*) /$1 break;
      proxy_pass http://mailer;
      proxy_redirect off;
    }

    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;

    rewrite ^/?(.*) /$1 break;
    proxy_pass http://cms;
    proxy_redirect off;
  }
}
