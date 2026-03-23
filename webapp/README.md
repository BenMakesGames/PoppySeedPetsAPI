## Running Locally

* `ng serve` runs with SSL. this is required for using secure cookies locally.
* it looks for the files `../ca.key` and `../ca.pem`
  * if you don't have those files, generate them with `openssl.exe`, ex:
    * `&"C:\Program Files\Git\usr\bin\openssl.exe" req -x509 -nodes -new -sha512 -days 365 -newkey rsa:4096 -keyout ca.key -out ca.pem -subj "/C=US/CN=MY-CA"`

