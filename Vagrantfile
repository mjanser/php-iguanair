Vagrant.configure('2') do |config|
  config.vm.box = 'obnox/fedora23-64-lxc'

  config.vm.provision 'shell' do |s|
    s.keep_color = true
    s.inline = <<SCRIPT
dnf -y install git php-cli php-process php-xml php-zip php-pecl-xdebug

if [ ! -f /usr/local/bin/composer.phar ]; then
  curl -s https://getcomposer.org/installer > composer-setup.php
  php composer-setup.php --install-dir=/usr/local/bin
  rm composer-setup.php
else
  /usr/local/bin/composer.phar self-update
fi

cd /vagrant
sudo -u vagrant /usr/local/bin/composer.phar update
SCRIPT
  end
end
