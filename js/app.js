﻿(function () {
  'use strict';

  var app = angular
          .module('app', ['vcRecaptcha', 'ngRoute', 'ngCookies']);

  app.config(config)
          .run(run);

  app.filter("sanitize", ['$sce', function ($sce) {
      return function (htmlCode) {
        return $sce.trustAsHtml(htmlCode);
      }
    }]);

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

  run.$inject = ['$rootScope', '$location', '$cookieStore', '$http', '$route'];
  function run($rootScope, $location, $cookieStore, $http, $route) {
    // keep user logged in after page refresh
    $rootScope.globals = $cookieStore.get('globals') || {};
    if ($rootScope.globals.currentUser) {
      $http.defaults.headers.common['Authorization'] = 'Basic ' + $rootScope.globals.currentUser.authdata; // jshint ignore:line
    }

    $rootScope.$on('$locationChangeStart', function (event, next, current) {
      // redirect to login page if not logged in and trying to access a restricted page
      var restrictedPage = $.inArray($location.path(), ['/login', '/register']) === -1;
      var loggedIn = $rootScope.globals.currentUser;
      if (restrictedPage && !loggedIn) {
        $location.path('/login');
      }
    });

    $route.reload();

  }

})();