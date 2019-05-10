[Unit]
Description=Consul Template
Requires=network-online.target
After=network-online.target consul-client.service nomad.service

[Service]
ExecStart=/bin/consul-template -template \
"/home/${userName}/templates/nginx.conf.tpl:/etc/nginx/sites-available/default:sudo systemctl reload nginx"
ExecReload=/bin/kill -HUP $MAINPID
KillMode=process
KillSignal=SIGTERM
Restart=on-failure
RestartSec=42s

[Install]
WantedBy=multi-user.target
