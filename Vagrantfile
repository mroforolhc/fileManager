# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/bionic64"

  config.vm.provider "virtualbox" do |v|
    v.name = "File Manager"
    v.memory = 4096
    v.cpus = 1
  end

  # Choose a custom IP so this doesn't collide with other Vagrant boxes
  config.vm.network "private_network", ip: "192.168.88.188"

  # Execute shell script(s)
  config.vm.provision :shell, path: "vagrant/apache.sh"
  config.vm.provision :shell, path: "vagrant/php.sh"
  config.vm.provision :shell, inline: "sudo locale-gen ru_RU.UTF-8"

  config.vm.synced_folder ".", "/var/www", create: true
end
