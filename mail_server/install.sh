yum -y install npm
yum -y install postfix
yum -y install dovecot
yum -y install cyrus-sasl cyrus-sasl-lib cyrus-sasl-plain  cyrus-sasl-devel
yum -y php
yum -y php-fpm
yum -y nginx
yum -y mariadb
yum install  mariadb-server

useradd hepm
passwd hepm


service postfix restart
service dovecot restart
service saslauthd restart
service php-fpm restart
service nginx restart
service mariadb restart
chkconfig dovecot on
chkconfig postfix on
chkconfig saslauthd on
chkconfig php-fpm on
chkconfig nginx on
chkconfig mariadb on