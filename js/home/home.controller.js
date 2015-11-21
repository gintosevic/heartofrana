(function () {
  'use strict';
  angular
          .module('app')
          .controller('HomeController', HomeController);
  HomeController.$inject = ['$location', 'AuthenticationService', 'FlashService'];
  function HomeController($location, AuthenticationService, FlashService) {
    config.$inject = ['$routeProvider', '$locationProvider'];
    function config($routeProvider, $locationProvider) {
      $routeProvider
              .when('/home', {
                controller: 'HomeController',
                templateUrl: 'html/home.view.html',
                controllerAs: 'vm'
              })

              .when('/login', {
                controller: 'LoginController',
                templateUrl: 'html/login.view.html',
                controllerAs: 'vm'
              })

              .when('/register', {
                controller: 'RegisterController',
                templateUrl: 'html/register.view.html',
                controllerAs: 'vm'
              })

              .otherwise({redirectTo: 'login'});
    }
  }
}

)();