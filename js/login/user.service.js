(function () {
  'use strict';

  angular
          .module('app')
          .factory('UserService', UserService);

  UserService.$inject = ['$http'];
  function UserService($http) {
    var service = {};

    service.GetByUsername = GetByUsername;
    service.Create = Create;
    service.Update = Update;
    service.Delete = Delete;

    return service;

    function GetByUsername(username) {
      $http.defaults.headers.post["Content-Type"] = "application/json";
      return $http.post('php/scripts/check-session.php', 'username=' + username).then(handleSuccess, handleError('Error getting user by username'));
    }

    function Create(user) {
//      $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
//      return $http.post('php/scripts/register.php', 'username='+user.username+'&password='+user.password+'&email='+user.email).then(handleSuccess, handleError('Error creating user'));
      return $http.post('php/scripts/register.php', user).then(handleSuccess, handleError('Error creating user'));
    }

    function Update(user) {
      return $http.put('/api/users/' + user.id, user).then(handleSuccess, handleError('Error updating user'));
    }

    function Delete(username) {
      return $http.delete('/api/users/' + id).then(handleSuccess, handleError('Error deleting user'));
    }

    // private functions

    function handleSuccess(data) {
      return data;
    }

    function handleError(error) {
      return function () {
        return {success: false, message: error};
      };
    }
  }

})();
