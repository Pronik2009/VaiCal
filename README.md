# VaiCal
### REST API for VaiCal app

This symfony project has 2 functionalities: 
**REST for some site\app** and **parsing texts generated with [GCal 11](http://www.krishnadays.com/)**

REST based on [API platform](https://api-platform.com/). Have next abilities:
1. _GET_ cities or city
2. _GET_ years or year
3. _POST_ new city, if your app not found it in city list.

[EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) as GUI:
1. List of saved cities
2. List of saved years
3. **Parser** for old VaiCal text files and for GCal 11 output. After successful validation, parsed data will be save to DB
4. List of new city request
_User management_ was not realized as usually, common test admin with wake password create for developing purposes.
**Don't forget create secure accounts on production!**

### TODO:
1. Unit tests for API Context
2. Other tests (parsing and functional)
