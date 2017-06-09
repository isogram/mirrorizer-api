FROM diegomarangoni/hhvm:cli
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
CMD [ "hhvm", "./public/index.php" ]
