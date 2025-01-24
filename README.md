

# Finding about code
1.	Code should be divided in multiple controllers like JobController, NotificationController and History Controller
2.	Controller must not contain logic, a service should be added where business logic should happen
3.	Validation is missing, There must be a RequestForm added for validation
4.	Standardize response, there must the same type of response of each request
5.	Should use Resource Class for response instead of modals
6.	Config variables should be taken with config file instead of enc file
7.	While doblock of method definition exist also is should contain more details
8.	Queries are directly called from controller which is wrong
9.	Hard to read code, it should be simple and easy to understand logic
10.	Naming conventions are inconsistent, it should be same throughout the code



### Improvemnt in Controllers
1. split the code into proper FormRequest, Services structure and removed logic from controller
2. Split single controller into 3 controllers with relevant functions
3. Standardized the response for all requests

**_NOTE:_** For Jobs in response Added JobResource class that will be used to return Jobs data instead of return Job Modal

### Improvemnt in Repository
1. Improve code for better readability 
2. Removed env usages and used config instead



## Unit Test
Unit test has been added for TeHelper class, That can give the idea how i do unit test





