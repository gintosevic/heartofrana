(function () {
    'use strict';

    angular
        .module('app')
        .controller('HomeController', HomeController);

    HomeController.$inject = ['UserService', '$rootScope'];
    function HomeController(UserService, $rootScope) {
        var vm = this;

        vm.user = null;
        vm.deleteUser = deleteUser;
        
        initController();

        function initController() {
        }

        function deleteUser(id) {
            UserService.Delete(id);
        }
    }

})();