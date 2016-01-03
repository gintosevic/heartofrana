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
         .controller('TradeController', TradeController);

  TradeController.$inject = ['$http', 'AuthenticationService', 'FlashService'];
  function TradeController($http, AuthenticationService, FlashService) {
    this.prices = [];

    initController(this);

    function initController(ctrl) {
      /* Get the news */
      return $http.get('php/scripts/get-prices.php').then(
              function (response) {
                ctrl.prices = response.data;
              },
              function (error) {
                if (AuthenticationService.isConnected()) {
                  FlashService.Error("Error while retrieving information about trade");
                }
              });
    }

  }

})();