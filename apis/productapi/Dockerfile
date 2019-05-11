FROM ubuntu:18.04
  
LABEL Kevin Cochran "kcochran@hashicorp.com"

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y python3 python3-pip
RUN mkdir /app

COPY ./requirements.txt /app/requirements.txt

WORKDIR /app

RUN pip3 install -r requirements.txt

RUN /bin/bash -c 'printf "DNS=127.0.0.1\nDomains=~consul" >> /etc/systemd/resolved.conf'
RUN iptables -t nat -A OUTPUT -d localhost -p udp -m udp --dport 53 -j REDIRECT --to-ports 8600
RUN iptables -t nat -A OUTPUT -d localhost -p tcp -m tcp --dport 53 -j REDIRECT --to-ports 8600
RUN service systemd-resolved restart

COPY ./product-app.py /app

ENTRYPOINT [ "python3" ]

CMD [ "product-app.py" ]
