![The Ice Hotel on Mount Frost](/assets/the-ice-hotel-mount-frost.jpg "Photograph of Mount Frost, view from above")

# The Ice Hotel

A repository for the assignment Yrgopelag at Yrgo.

In short, the assignment was to create a hotel booking site for a fictional hotel and store bookings in a SQL database.

The hotel qualifies for a star rating (1-5) based on the following criteria:

- The hotel website has a graphical presentation.
- The hotel website can give discounts.
- The hotel can offer at least one feature.
- The hotel can use external data when producing successful booking responses.
- The hotel has an admin page where for example room prices could be changed.

Visit the site [here](https://php-fanclub.se/the-ice-hotel/)

## Mount Frost

A frozen mountain in the sea (see image above).

## The Ice Hotel

Welcome to The Ice Hotel, the crown jewel of Mount Frost: A hotel completely carved out of ice.

Star rating  - ⭐⭐⭐⭐⭐

# Instructions

### To run the project you need to have the following installed:

- PHP & Composer
- Node & NPM

### To generate files which are not included (see .gitignore), run the following commands:

- `composer install`
- `npm i`
- `npm run build`

### To make a successful booking without a transfer code, for testing:

- In php/payment.php, uncomment line 39 - `// if (1 === 1) { // used for testing instead of line below`
- In php/payment.php, comment out line 40 - `if ($transferCodeStatus->transferCode === $transferCode) {`

### To test the admin page and login/logout from it:

- Create a file called .env (see .env.example) and set API_KEY to some value you then also use as password.

### Make sure filepath base URL is correctly set:

- In php/autoload.php, make sure `$baseUrl = "/";` is true. This setting works when testing locally.
- `$baseUrl = "https://php-fanclub.se/the-ice-hotel/";` should be commented out. This setting is used for deployment.

# Code review

1. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
2. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
3. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
4. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
5. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
6. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
7. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
8. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
9. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
10. example.js:10-15 - Remember to think about X and this could be refactored using the amazing Y function.
