# Gravity Forms HTTP Confirmation Conditions

Allows the sending of API requests to determine if a notification action should occur.

Another plugin made for a client that fell under an open source license/"mantenance benefits if I can release it" agreement.

Like lots of the other Gravity Forms add-ons here, it was made ages ago - if it even works at all anymore, it's probably not anywhere near production ready.

With this one in particular, you're going to want to be careful with this - I doubt it does much sanitization/validation and if it does, it was probably built for a very specific purpose where everything was controlled. Don't go sucking in arbitrary crap into your confirmations.

## Old Notes...

### Usage

1. Create a form. The form must contain an email field.
2. Access the *HTTP Confirmation Conditions* screen on the feed settings.
3. Create a feed, mapping all fields. Fields are directly linked to the URL query string.
4. Create notifications with the customer exists/does not exist conditions.