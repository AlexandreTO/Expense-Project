# Expense Project

A small project for now. This is just an expense project that I am working on to hone / expand my skills. 

I will add more functionalities once I feel like the base is done and whenever I find a good implementation / idea for it.

## Setup

```Bash
composer install
npm install
npm run dev
symfony server:start
php bin/console doctrine:migrations:migrate # For migrating in the database
```

## Testing

```Bash
vendor/bin/phpunit #can add the folder or files to specify which tests to run instead of all
```

### TODO

- [x] Routing
- [x] Database
- [x] Controller and Entity
- [x] API
  - ~~Make an interface for it instead of having to use cURL or Postman.~~
  - Secure the access to the API
- [x] Unit Testing
  - Need to integrate properly the API tests in the workflow
  - Implement fixtures to be able to properly test without having to put manual data
- [x] CI / CD with Github actions
- [ ] Design (this will be later)
- [x] Export to CSV
- [ ] A search bar if possible

Alexandre TO