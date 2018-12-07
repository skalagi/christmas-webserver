#!/usr/bin/env bash

echo 14 > /sys/class/gpio/export
echo 15 > /sys/class/gpio/export
echo 18 > /sys/class/gpio/export
echo 23 > /sys/class/gpio/export

echo out > /sys/class/gpio/gpio14/direction
echo out > /sys/class/gpio/gpio15/direction
echo out > /sys/class/gpio/gpio18/direction
echo out > /sys/class/gpio/gpio23/direction

echo 1 > /sys/class/gpio/gpio14/value
echo 1 > /sys/class/gpio/gpio15/value
echo 1 > /sys/class/gpio/gpio18/value
echo 1 > /sys/class/gpio/gpio23/value