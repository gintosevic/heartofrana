(function () {
  'use strict';
  var app = angular.module('app', ['ui.router', 'vcRecaptcha', 'ngRoute', 'ngCookies']);
//    app.config(config);
  app.config(config)
          .run(run);
  app.filter("sanitize", ['$sce', function ($sce) {
      return function (htmlCode) {
        return $sce.trustAsHtml(htmlCode);
      }
    }]);
  config.$inject = ['$stateProvider', '$routeProvider', '$locationProvider'];
//  config.$inject = ['$stateProvider'];
  function config($stateProvider, $routeProvider, $locationProvider) {
    $stateProvider
            .state('unconnected', {
              abstract: true,
              views: {
                'mainFrame': {
                  templateUrl: 'html/unconnected-index.html'
                }
              }
            })
            .state('unconnected.login', {
              url: "/login",
//              views: {
//                'tabFrame': {
              templateUrl: 'html/login.view.html',
              controller: 'LoginController',
              controllerAs: 'vm'
//                }
//              }
            })
            .state('unconnected.register', {
              url: "/register",
//              views: {
//                "tabFrame": {
              templateUrl: "html/register.view.html",
              controller: 'RegisterController',
              controllerAs: 'vm'
//                }
//              }
            })

            .state('connected', {
              abstract: true,
              views: {
                'mainFrame': {
                  templateUrl: 'html/connected-index.html'
                }
              }
            })
            .state('connected.news', {
              url: "/news",
              templateUrl: "html/news.view.html",
              controller: 'NewsController',
              controllerAs: 'newsCtrl'
            })
            .state('connected.map', {
              url: "/map",
              templateUrl: "html/map.view.html",
              controller: 'MapController',
              controllerAs: 'mapCtrl'
            })
            .state('connected.planets', {
              url: "/planets",
              templateUrl: "html/planets.view.html",
              controller: 'PlanetsController',
              controllerAs: 'planetsCtrl'
            })
            .state('connected.science', {
              url: "/science",
              templateUrl: "html/science.view.html",
              controller: 'ScienceController',
              controllerAs: 'scienceCtrl'
            })
            .state('connected.fleet', {
              url: "/fleet",
              templateUrl: "html/fleet.view.html",
              controller: 'FleetController',
              controllerAs: 'fleetCtrl'
            })

            .state('unconnected.otherwise', {
              url: '*path',
              templateUrl: "html/login.view.html",
              controller: 'LoginController',
              controllerAs: 'vm'
            });
//  }
    $routeProvider.otherwise('/otherwise')
//    $routeProvider
//            .when('/home', {
//              controller: 'HomeController',
//              templateUrl: 'html/home.view.html',
//              controllerAs: 'vm'
//            })
//
//            .when('/login', {
//              controller: 'LoginController',
//              templateUrl: 'html/login.view.html',
//              controllerAs: 'vm'
//            })
//
//            .when('/register', {
//              controller: 'RegisterController',
//              templateUrl: 'html/register.view.html',
//              controllerAs: 'vm'
//            })
//
//            .otherwise({redirectTo: 'login'});
  }
//
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
        $state.go('unconnected.login');
      }
    });
//    $route.reload();
  }

})();