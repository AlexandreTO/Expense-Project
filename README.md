# Expense Project

A small project for now. This is just an expense project that I am working on to hone / expand my skills mainly on the backend side. 

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

- [ ] Unit Testing
  - Need to integrate properly the API tests in the workflow
  - Update the tests and make them more robust
- [ ] Add redis for caching purpose
- [ ] Redo the project to setup the docker easier

Alexandre TO