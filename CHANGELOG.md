# [ALPHA]
## [04.03.2026]
- Polishing README.md
- Redis cache for category and matching score.
- Polishing the front
- Small fixes for fixture, tests and role admin
## [03.03.2026]
- Better toast notification when error occurs
- Rate limiter and global CSRF protection
## [02.03.2026]
- Implementing Loader, Toast and BaseModal components
- Adding maxPrice field for User
## [28.02.2026]
- Wishes recommendation with notifications
## [27.02.2026]
- WishItemSearchPage with filtering and copy function
- Fix for SearchComponent and Stores
- Changing WishItemMine to WishItemList to better reusable
- Fix for user ApiResource - changing uri api/users/me to api/user/me - previous version causes problems with ApiResource should be used calling api/users/{}
## [24.02.2026]
- Fully working user wishlist view + small global fixes
- Currency and WishItem management functionality
## [19.02.2026]
- Fully functional Profile view and data management and universal SearchComponent and API services
- Service handling tags used in users and wishItems
- Adding file upload for avatars (flysystem)
## [18.02.2026]
- Tests for api resources with authentication and authorization
- Fix for CountryStore and Search for countries
## [17.02.2026]
- Email verification - email service with verification link and verifying email after registration
- Login and registration with validation and error messages and country selector with store and api service
- installing axios and configuration for api calls
## [16.02.2026]
- Simple tests for api resources and LinkValidator
- ApiResource and validation config for entites with register/login DTO + Factories and Fixture for all entities
- Entities: Category, Country, Currency, Tag, User, WishItem
## [15.02.2026]
- MainController with first version of landing page and styling
- Frontend configuration
## [13.02.2026]
- First configuration for new symfony 6 project with PHP 8.2, Nginx and MySql containers on docker