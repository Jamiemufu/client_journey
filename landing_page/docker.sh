#!/bin/sh
tput reset
echo "########################################"
echo "# Start a Docker container for Whiskey #"
echo "########################################"
echo ""
echo "What port should the container be accessible over?"
echo ""
read container_port
docker_exists=`which docker`
`nc -z -v -w5 localhost $container_port`
port_in_use=$?
tput reset
echo "########################################"
echo "# Start a Docker container for Whiskey #"
echo "########################################"
echo ""
if [ ${#docker_exists} -lt 2 ]; then
    echo "Please install Docker and try again"
    echo ""
elif [ "$port_in_use" != 0 ]; then
    echo "Once started, you can access your application from http://localhost:$container_port"
    echo ""
    echo "What should the Docker container be called?"
    echo ""
    read container_name
    echo ""
    docker stop $container_name
    docker rm $container_name
    docker run -p $container_port:80 -v $PWD:/var/www/ --name $container_name -t -i -d whsky/lamp:v5 apachectl -D FOREGROUND
    docker exec -i -t $container_name service mysql start
    docker exec -i -t $container_name cron
    tput reset
    echo "############################"
    echo "# Docker container started #"
    echo "############################"
    echo ""
    echo "phpMyAdmin is available at http://localhost:$container_port/phpmyadmin"
    echo "(root password is 'root')"
    echo ""
else
    echo "Please stop any other services/containers listening on port $container_port and try again:"
    echo ""
    docker ps
    echo ""
fi