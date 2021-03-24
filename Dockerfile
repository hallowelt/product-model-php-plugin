FROM ubuntu:20.04 as buildbase
ENV TZ=CET
ENV DEBIAN_FRONTEND=noninteractive
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
 && apt-get update \
 && mkdir /root/.ssh \
 && apt-get install --no-install-recommends -y ca-certificates wget patch openssh-client git php-cli unzip curl php-tokenizer php-xml php-mbstring php-common php-curl php-gd php-intl php-json php-ldap php-mysql php-opcache php-tidy php-zip php-pear \
 && echo "StrictHostKeyChecking no" > /root/.ssh/config \
 && curl -sS https://getcomposer.org/installer -o composer-setup.php \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer --1 \
 && mkdir /opt/scripts \
 && mkdir /opt/workspace \
 && rm -Rf /var/lib/apt/lists/* \
 && rm -Rf /tmp/*
COPY includes/run.sh /opt/
RUN chmod -Rf 600 /root/.ssh \
 && chmod +x /opt/*.sh
ENTRYPOINT /opt/run.sh
