/* 
 * Copyright (C) 2015 gint
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

(function () {
  'use strict';

  angular.module('app')
          .controller('PlanetsController', PlanetsController);

  PlanetsController.$inject = ['$http', 'AuthenticationService', 'FlashService'];
  function PlanetsController($http, AuthenticationService, FlashService) {
    this.allPlanets = [];

    initController(this);

    function initController(ctrl) {

      /* Get the news */
      return $http.get('php/scripts/get-planets.php').then(
              function (response) {
                ctrl.allPlanets = response.data;
              },
              function (error) {
                if (AuthenticationService.isConnected()) {
                  FlashService.Error("Error while retrieving information about your planets");
                }
              });
    }

  }

  angular.module('app')
          .directive('planet', function () {
            return {
              restrict: 'E',
              scope: {'planet': '=data'},
              templateUrl: "html/partials/planet.html"
            };
          });

  angular.module('app')
          .directive('planetImage', function () {
            return {
              restrict: 'E',
              scope: {'systemId': '@systemId',
                'position': '@position',
                'maxSize': '@maxSize'},
              templateUrl: "html/partials/planet-image.html"
            };
          });



  angular.module('app')
          .directive('dial', function () {
            return {
//      template: 'salut'
              restrict: 'E',
              transclude: true,
              scope: {'id': '@id',
                'color': '@color',
                'progress': '@progress',
                'radius': '@radius',
                'border': '@border'
              },
              templateUrl: 'html/partials/dial.html'
            };
          });

//  angular.module('app')
// .directive('dial', function () {
//   return {
//     restrict: 'E',
//     scope: {'dial': '=data'},
//     templateUrl: "html/partials/dial.html"
//   };
// });

})();