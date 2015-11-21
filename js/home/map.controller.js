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
         .controller('MapController', MapController);

  MapController.$inject = ['$http', '$scope', 'AuthenticationService', 'FlashService'];
  function MapController($http, $scope, AuthenticationService, FlashService) {
    this.visibleSystems = [];

    initController(this);
    
    function initController(ctrl) {
//      document.mapPanZoom.updateViewPort();
      /* Get the news */
      return $http.get('php/scripts/get-map.php').then(
              function (response) {
                ctrl.visibleSystems = response.data;
              },
              function (error) {
                if (AuthenticationService.isConnected()) {
                  FlashService.Error("Error while screening the map");
                }
              });
    }
    
    this.drawMap = function() {
//      var map = SVG('map');
      for (var index = 0; index < this.visibleSystems.length; index++) {
        alert(index);
        console.log(this.visibleSystems[index]);
      }
    }

  }

})();