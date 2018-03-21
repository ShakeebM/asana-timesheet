/**
 * Created by netstager on 03/09/14.
 */
var app = angular.module('myApp', ["timer"]).filter('millSecondsToTimeString', function() {
    return function(millseconds) {
        var seconds = Math.floor(millseconds / 1000);
        //var days = Math.floor(seconds / 86400);
        var hours = Math.floor(seconds  / 3600);
        var minutes = Math.floor((seconds  % 3600) / 60);
        var secondsdone = Math.floor(seconds % 3600 % 60);
        var timeString = '';
       // if(days > 0) timeString += (days > 1) ? (days + " days ") : (days + " day ");
        if(hours >= 0) timeString += (hours > 1) ? (hours + " : ") : (hours + " : ");
        if(minutes >= 0) timeString += (minutes > 1) ? (minutes + " : ") : (minutes + " : ");
        if(secondsdone >=0) timeString +=(secondsdone >60 ) ? (secondsdone-60) :(secondsdone)
        return timeString;
    }
});
//var srcurl='http://192.168.1.50:8231/PRJCTRC/';
var srcurl='';
app.controller('UserController', function ($scope, $http) {

    //initial method for initializing variables and getting modules from api
    $scope.status=-1;
    $scope.timeelapsed= 0;
    $scope.loaderstat=0;
    $scope.init= function(){
        if(localStorage.user) {
            $scope.uid = localStorage.user;
            $http({method: 'GET', url: srcurl+'asana/user/useractions.php?action=getuserdata&uid=' + $scope.uid})
                .success(function (data, status) {
                    $scope.response = data;
                    $scope.getTaskByUser();
                    $scope.searchByDatetoday();
                    $scope.searchByDatemonth();
                    $scope.getAllProjects();
                })
        }
        //}
        //else{
        //    window.location.href="login.html"
        //}
    }

//
//    $scope.getUserDetails=function(uid){
//        $http({method: 'GET',url: 'asana/user/useractions.php?action=&uid='+uid})
//            .success(function (data,status){
//                $scope.response=data.data;
//               // this.getTaskByUser(uid,data.data.workspaces[0].id)
//             //   angular.element('#linearcontent').scope().getTaskByUser(uid,data.data.workspaces[0].id)
//
//            })
//            .error(function (data,status){
//
//            })
//    }
    $scope.getTaskByUser= function (){
        $http({method: 'GET',url: srcurl+'asana/user/useractions.php?action=gettaskdata&userid='+localStorage.user})
            .success(function (data,status){
                $scope.taskinfo=data;
                $scope.timeRunning(data);
                console.log(data);
                $scope.tasktodo=0;
                angular.forEach(data, function(value, key) {
                    if(value.status==0) {
                        $scope.tasktodo += 1;
                    }
                })


        })
            .error(function (data,status){

            })

    }
    $scope.getAllProjects= function(){
        $http({method:'GET', url:'asana/admin/adminactions.php?action=getProjects'})
            .success(function(data,status){
                $scope.projectdata=data;
                console.log(data);

            }).error(function(data,status){console.log(data)});
    }



    $scope.searchByDatetoday= function(){
                var today = new Date();
                var dateString = today.format("yyyy-mm-dd");
            $http({method: 'GET',url: 'asana/user/useractions.php?action=searchviadate&userid='+localStorage.user+'&datechoosefrom='+dateString+'&datechooseto=0'})
                .success(function (data,status){
                    console.log(data)
                    var timetake=0;
                    angular.forEach(data, function(value, key) {
                        timetake=parseInt(timetake)+parseInt(value.totaltime);
                    })
                    $scope.timeworkedtoday=timetake;
                })
                .error(function (data,status){
                    console.log(status)
                })
        }
    $scope.searchByDatemonth= function(){
        var today = new Date();
        console.log(today)
        var datetoday = today.format("yyyy-mm-dd");
        var firstday= today.format("yyyy-mm")+'-01';
        $http({method: 'GET',url: 'asana/user/useractions.php?action=searchviadate&userid='+localStorage.user+'&datechoosefrom='+firstday+'&datechooseto='+datetoday})
            .success(function (data,status){
                console.log(data)
                //$scope.filterdhistory=data;
                var timetake=0;
                angular.forEach(data, function(value, key) {
                    timetake=parseInt(timetake)+parseInt(value.totaltime);
                })
                $scope.timeworkedmonth=timetake;
            })
            .error(function (data,status){
                console.log(status)
            })
    }



    $scope.syncTasks= function(){
        $scope.loaderstat=1;
        $http({method: 'GET',url: srcurl+'asana/user/useractions.php?action=synctasks&userid='+localStorage.user}).
            success(function (data,status){
                $scope.loaderstat=0;
                $scope.getTaskByUser();
            });
    }


    $scope.timeRunning= function(taskinfo){

        angular.forEach(taskinfo,function (valuex, index) {
                if(valuex.timestamp>0) {

                    $scope.timeelapsed = valuex.timestamp * 1;

                    $scope.status = index;

                }
        })

    }
    $scope.starttimer= function(){
        //$scope.timeelapsed=valuex.timestamp;
//       $('#my_timer').attr('start-time',1410785876076);
        //$scope.timeelapsed=0;

        $scope.$broadcast('timer-start');
    }
//
//
//
//
    $scope.getTimeStamp= function(taskid){
        var date=Date.now();
        $http({method: 'GET',url: 'asana/user/useractions.php?action=settimestamp&taskid='+taskid+'&time='+date})
            .success(function (data,status){

                $scope.$broadcast('timer-start');
                console.log('tasks!!!!')
                $scope.getTaskByUser(localStorage.user);

            })
            .error(function (data,status){

            })
    }
    $scope.stopTimeStamp= function(taskid) {
        var date = Date.now();
        $http({method: 'GET', url: 'asana/user/useractions.php?action=addtimetask&taskid='+ taskid + '&time=' + date})
            .success(function (data, status) {
                $scope.$broadcast('timer-stop');
                $scope.timeelapsed = 0;
                $scope.status = -1;
                $scope.getTaskByUser(localStorage.user);
                $scope.searchByDatetoday();
                $scope.searchByDatemonth();

            })
            .error(function (data, status) {
                console.log(status)
            })
    }
    $scope.addAtask= function(tasktoAddInfo){

        $http({method:'GET', url:'asana/user/useractions.php?action=addnewTask&userid='+localStorage.user+'&taskInput='+ JSON.stringify(tasktoAddInfo)  })
            .success(function(data,status){
                window.location.href="index.html"
            }).error(function(data,status){

            })

    }

    $scope.logout= function(){
      //  $scope.uid='';
        localStorage.user="";
        window.location.href="login.html";
        $scope.status=-1;
        $scope.response=[];
        $scope.taskinfo=[];
        $scope.timeelapsed=0;
        $scope.historytasks=[];
        //$scope.filterdhistory=[];
    }
//
//
//    $scope.$on('timer-stopped', function (event, data){
//        angular.element('#linearcontent').scope().getTaskByUser($scope.uid,$scope.response.workspaces[0].id);
//        console.log('Timer Stopped - data = ', data);
//    });
//
//
});

var dateFormat = function () {
    var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
        timezoneClip = /[^-+\dA-Z]/g,
        pad = function (val, len) {
            val = String(val);
            len = len || 2;
            while (val.length < len) val = "0" + val;
            return val;
        };

    // Regexes and supporting functions are cached through closure
    return function (date, mask, utc) {
        var dF = dateFormat;

        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
            mask = date;
            date = undefined;
        }

        // Passing date through Date applies Date.parse, if necessary
        date = date ? new Date(date) : new Date;
        if (isNaN(date)) throw SyntaxError("invalid date");

        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

        // Allow setting the utc argument via the mask
        if (mask.slice(0, 4) == "UTC:") {
            mask = mask.slice(4);
            utc = true;
        }

        var _ = utc ? "getUTC" : "get",
            d = date[_ + "Date"](),
            D = date[_ + "Day"](),
            m = date[_ + "Month"](),
            y = date[_ + "FullYear"](),
            H = date[_ + "Hours"](),
            M = date[_ + "Minutes"](),
            s = date[_ + "Seconds"](),
            L = date[_ + "Milliseconds"](),
            o = utc ? 0 : date.getTimezoneOffset(),
            flags = {
                d:    d,
                dd:   pad(d),
                ddd:  dF.i18n.dayNames[D],
                dddd: dF.i18n.dayNames[D + 7],
                m:    m + 1,
                mm:   pad(m + 1),
                mmm:  dF.i18n.monthNames[m],
                mmmm: dF.i18n.monthNames[m + 12],
                yy:   String(y).slice(2),
                yyyy: y,
                h:    H % 12 || 12,
                hh:   pad(H % 12 || 12),
                H:    H,
                HH:   pad(H),
                M:    M,
                MM:   pad(M),
                s:    s,
                ss:   pad(s),
                l:    pad(L, 3),
                L:    pad(L > 99 ? Math.round(L / 10) : L),
                t:    H < 12 ? "a"  : "p",
                tt:   H < 12 ? "am" : "pm",
                T:    H < 12 ? "A"  : "P",
                TT:   H < 12 ? "AM" : "PM",
                Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
            };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    "default":      "ddd mmm dd yyyy HH:MM:ss",
    shortDate:      "m/d/yy",
    mediumDate:     "mmm d, yyyy",
    longDate:       "mmmm d, yyyy",
    fullDate:       "dddd, mmmm d, yyyy",
    shortTime:      "h:MM TT",
    mediumTime:     "h:MM:ss TT",
    longTime:       "h:MM:ss TT Z",
    isoDate:        "yyyy-mm-dd",
    isoTime:        "HH:MM:ss",
    isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
    dayNames: [
        "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
        "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    monthNames: [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
        "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
    ]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
    return dateFormat(this, mask, utc);
};





