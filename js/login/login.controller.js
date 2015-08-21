(function () {
  'use strict';

  angular
          .module('app')
          .controller('LoginController', LoginController);

  LoginController.$inject = ['$location', 'AuthenticationService', 'FlashService'];
  function LoginController($location, AuthenticationService, FlashService) {
    var vm = this;

    vm.login = login;
    vm.logout = logout;

    (function initController() {
      // reset login status
      if (AuthenticationService.hasCurrentUser()) {
        AuthenticationService.CheckSession(
                AuthenticationService.getCurrentUserName(),
                function (response) {
                  if (!response.data.success) {
                    if (AuthenticationService.isConnected()) {
                      FlashService.Error("The session expired");
                    }
                    AuthenticationService.ClearCredentials();
                  }
                },
                function (error) {
                  FlashService.Error(error.message);
                }
        );
      }
      else {
        AuthenticationService.ClearCredentials();
      }
    })();

    function login() {
      vm.dataLoading = true;
      AuthenticationService.Login(vm.username, vm.password, function (response) {
        if (response.success) {
          AuthenticationService.SetCredentials(vm.username, vm.password);
          $location.path("/home");
        } else {
          FlashService.Error(response.message);
          vm.dataLoading = false;
        }
      });
    }


    function logout(superCtrl) {
      vm.dataLoading = true;
      AuthenticationService.Logout(function (response) {
        if (response.success) {
          AuthenticationService.ClearCredentials(vm.username, vm.password);
          $location.path("/login");
          FlashService.Success("You have been sucessfully disconnected");
        } else {
          FlashService.Error(response.message);
          vm.dataLoading = false;
        }
      });
    }
  }

})();
