#!/usr/bin/env bash

echo $1 > /sys/class/gpio/gpio14/value
echo $2 > /sys/class/gpio/gpio15/value
echo $3 > /sys/class/gpio/gpio18/value
echo $4 > /sys/class/gpio/gpio23/value