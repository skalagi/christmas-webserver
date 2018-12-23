#!/usr/bin/env bash

if [ -z $1 ]
then
    ONE=1
else
    ONE=$1
fi

if [ -z $2 ]
then
    TWO=1
else
    TWO=$2
fi


if [ -z $3 ]
then
    THREE=1
else
    THREE=$3
fi

if [ -z $4 ]
then
    FOUR=1
else
    FOUR=$4
fi

echo $ONE > /sys/class/gpio/gpio14/value
echo $TWO > /sys/class/gpio/gpio15/value
echo $THREE > /sys/class/gpio/gpio18/value
echo $FOUR  > /sys/class/gpio/gpio23/value