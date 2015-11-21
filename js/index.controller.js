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
          .controller('IndexController', IndexController);
  
  IndexController.$inject = ['$rootScope'];
  
  function IndexController($rootScope) {
            this.tab = 'news';
            
            this.isConnected = function () {
              return $rootScope.globals.isConnected;
            }
            
            this.displayNews = function() {
              this.tab = 'news';
            }
            
            this.displayMap = function() {
              this.tab = 'map';
            }
            
            this.displayPlanets = function() {
              this.tab = 'planets';
            }
            
            this.displayScience = function() {
              this.tab = 'science';
            }
            
            this.displayTrade = function() {
              this.tab = 'trade';
            }
            
            this.displayAlliance = function() {
              this.tab = 'alliance';
            }
            
            this.displayFleets = function() {
              this.tab = 'fleets';
            }
          };
})();
