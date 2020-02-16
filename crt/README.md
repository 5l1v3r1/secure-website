# SSL/TLS Certificate

Generated with Java tool called `keytool`. You can find it inside Java's `\bin\` directory. You need to have Java installed, download it from [here](https://www.java.com/en/download/).

Generated with Java v8 update 231 on Windows 10 Enterprise OS (64 bit).

## How to Run

Open the Command Prompt and run the commands shown below.

If the `%JAVA_HOME%` environment variable is not set, then manually enter a full path to your Java's `\bin\` directory.

If no path is specified for generated files, they will default to the `\bin\` directory.

Generate a private key (do not share it with anyone):

```fundamental
"%JAVA_HOME%\bin\keytool" -genkeypair -keyalg RSA -alias sws -storetype PKCS12 -keystore "sws.key" -storepass securewebsite -validity 365 -keysize 2048
```

Generate a certificate from the private key:

```fundamental
"%JAVA_HOME%\bin\keytool" -exportcert -rfc -alias sws -file "sws.crt" -keystore "sws.key" -storepass securewebsite
```

Generate a certificate signing request (if you want to register your certificate to Certificate Authority):

```fundamental
"%JAVA_HOME%\bin\keytool" -certreq -alias sws -file "sws.csr" -keystore "sws.key" -storepass securewebsite
```

Generate a PEM format certificate (optional):

```fundamental
openssl pkcs12 -in sws.key -nocerts -out sws_key.pem -passin pass:securewebsite -passout pass:securewebsite

openssl pkcs12 -in sws.key -nokeys -out sws_crt.pem -passin pass:securewebsite
```

Download OpenSSL from [here](https://slproweb.com/products/Win32OpenSSL.html).

To use this tool, you need to manually navigate to the OpenSSL's `\bin\` directory or manually enter its full path.

If no path is specified for generated files, they will default to the `\bin\` directory.
