# Christmas lights WebServer

### Installing
```
composer install
```

### Configuring
```
vim Resources/config/parameters.yml
```

### Running
Step 1: Run mock of AVR module
```
php Resources/mock/worker.php [PORT]
```
PORT the same in `Resources/parameters.yml#avr_port`

Step 1: Run web-socket server
```
php wsserver.php
```