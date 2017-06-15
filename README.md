# Permalinks to WP-API plugin

**Permalinks to WP-API** is a WordPress plugin which retrieves a valid WP-API result from a permalink url.

This first version was developed during the WCEU 2017 Contributor Day by the [Worona](https://www.worona.org) Team.

## Why this?

Progressive web apps created with React, Angular or any other JS framework can retrieve and display content from WordPress programatically using the WP-API.

Post (or page) content retrieved from the WP-API usually contains links to another posts, pages, categories and so on. The problem is those links doesn't contain any useful information to get those results by fetching the WP-API.

## How to use it

This plugins registers a route called `permalinks/v1` and an endpoint called `discover`. That endpoint accepts a `url` parameter and retrieves the same content the WP-API would.

For example, if the post content rendered by your web app contains a link to `http://example.com/my-post` you can capture that click and start a fetch request to `http://example.com/wp-json/permalinks/v1/discover?url=/my-post
` instead. The result returned by that route should match the result of `http://example.com/wp-json/wp/v2/posts/1234` assuming 1234 is the ID of `my-post` post.

## Current State

We've used some tricks to know if the permalink belongs to a post, page, category, tag, author, custom taxonomy or custom post. We are open to explore any alternative approaches so if you have any other idea please open an issue and we can discuss it.
