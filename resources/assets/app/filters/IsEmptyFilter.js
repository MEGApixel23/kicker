(function (app) {
  app.filter('isEmpty', function isEmpty() {
    var bar;

    return function (obj) {
      for (bar in obj) {
        if (obj.hasOwnProperty(bar)) {
          return false;
        }
      }

      return true;
    };
  });
})(angular.module('kickerApp'));