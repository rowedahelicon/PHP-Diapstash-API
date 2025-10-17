# PHP-Diapstash-API
A simple php wrapper for the Diapstash API

**Warning - This is not complete by any means and is subject to changes as the API is developed**

This is a quick and simple wrapper for the [diapstash API](https://api.diapstash.com/)

# How to use
- Generate your login url with `getLoginUrl()`
- This will generate a url to authorize you or your user(s), it will also return a `code_verifier` that needs to be saved somehow to authenticate with `getToken()`
- When authorized by you or your user, it will return a `code` and a `state` in the URL, enter those in along with your `code_verifier` into `getToken()`
- `getToken()` will return a `access_token` and a `refresh_token`. The `access_token` needs to be passed into the class with `generateAuthHeader()`
- The `access_token` only works for an hour after it is generated. It needs to be refreshed when needed using your `refresh_token` being passed into `refreshToken()`
- `refresh_token`s live for 2 weeks, and are a one-time use token. New `refresh_token`s are generated and returned when you use `refreshToken()`
- After all of this is out of the way, you can then use the rest of the provided functions as needed.

Most of the API call functions take an array of key/values that correspond to URL queries. I'll write them out at some point when the API seems finished, until then, everything should be fairly straightforward with my comments.
I have provided a sample script to help make sense of it!

If you have any questions, do let me know!

If your question is "why did you make this", the answer is I felt like it.
