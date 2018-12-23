#!/usr/bin/env bash

if [ -z $1 ]
then
    $1 = 1
fi

if [ -z $2 ]
then
    $2 = 1
fi

if [ -z $3 ]
then
    $3 = 1
fi

if [ -z $4 ]
then
    $4 = 1
fi


echo $1 > /sys/class/gpio/gpio14/value
echo $2 > /sys/class/gpio/gpio15/value
echo $3 > /sys/class/gpio/gpio18/value
echo $4 > /sys/class/gpio/gpio23/value