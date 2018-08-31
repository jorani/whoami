
**Whoami** is a Demo application showing how to use Jorani as an Identity Provider.

This is an example of a SSO scenario based on OAuth2 protocol.

## Installation

1. Download or clone the repository.
2. Update the 3rd party libraries with `composer install`
3. Copy EXAMPLE.env to .env according to the values you will choose in the next paragraph.

## Instructions

1. You need a Jorani instance with at least v0.6.0 (e.g. https://demo.jorani.org/)

2. You must insert a new OAuth2 client into Jorani either with the WebUI (admin/OAuth clients) or with this query:

    INSERT INTO oauth_clients (client_id, client_secret, redirect_uri) 
    VALUES ("whoami", "pwdpwd", "https://whoami.jorani.org/");

Where:

* *client_id* identify the application.
* *client_secret* is a password used by the application (it's an account service not related to any Jorani user).
* *redirect_uri* is the URL of your application using Jorani as an IDP or consuming its REST API.

3. Edit the OAuth2 client configuration (e.g. the *.env* at the root of this demo application).

4. You can now open the 3rd party application (e.g. https://whoami.jorani.org/).

5. If you are not connected to your Jorani instance, you will be prompted to connect using your credentials (on the demo application https://demo.jorani.org/, the credentials are *bbalet* and *bbalet*).

6. If you are using the application for the first time, you'll be asked if you want to authorize it or not.

7. All authorized applications appear into *My personal information* (e.g. https://demo.jorani.org/users/myprofile).
