/**
 * Created by netstager on 18/09/14.
 */


var app = angular.module('myApp', []).filter('millSecondsToTimeString', function() {

    return function(millseconds) {
        var seconds = Math.floor(millseconds / 1000);
        var days = Math.floor(seconds / 86400);
        var hours = Math.floor((seconds % 86400) / 3600);
        var minutes = Math.floor(((seconds % 86400) % 3600) / 60);
        var secondsdone = Math.floor((seconds % 86400 % 3600 % 60))
        var timeString = '';
        if(days > 0) timeString += (days > 1) ? (days + " days ") : (days + " day ");
        if(hours > 0) timeString += (hours > 1) ? (hours + " hours ") : (hours + " hour ");
        if(minutes >= 0) timeString += (minutes > 1) ? (minutes + " minutes ") : (minutes + " minute ");
        if(secondsdone >=0) timeString +=(secondsdone >60 ) ? (secondsdone-60 + "seconds") :(secondsdone + "seconds")
        return timeString;

    }
});

var srcurl='asana/admin';

app.controller('AdminController', function($scope,$http){

    $scope.indexer=-1;
    $scope.loaderstat=0;
    $scope.userview=0;
    $scope.init= function(){
            if(!localStorage.admin){
                window.location.href='admin';
            }
        else{
                $scope.getallUsers()
            }
    }
    $scope.initproject= function(){
        this.getAllClients();
        this.getAllProjects();
        this.getLoginUsers();
    }
    $scope.initclients=  function(){
        this.getAllClients();
    }

    $scope.selectproject= function(id){
        $scope.projectselected=id;
    }


    //get data from db
    $scope.getallUsers= function(){
        $http({method: 'GET',url: srcurl+'/adminactions.php?action=getall'})
            .success(function(data,status){
                $scope.userslist=data;
            }).error(function(data,status){
            })
        //this.getLoginUsers();
        //this.getAllClients();
    };
    $scope.getLoginUsers= function(){
        $http({method: 'GET',url: srcurl+'/adminactions.php?action=getlogin'})
            .success(function(data,status){
                $scope.userslist=data;
                console.log(data)

            }).error(function(data,status){

            })
    }
    $scope.getAllClients= function(){
        $http({method:'GET', url: srcurl+'/adminactions.php?action=getclients'})
            .success(function(data,status){
                $scope.clientlist=data;

            }).error(function(data,status){
                console.log(data);
            });
    }

    $scope.getClientProjects= function(){
        $http({method:'GET', url: srcurl+'/adminactions.php?action=getclientprojects'})
            .success(function(data, status){
                $scope.projectbyclients=data;
            }).error(function(data, status){
                console.log(data);
            })

    }

    $scope.getAllProjects= function(){
        $http({method:'GET', url:srcurl+'/adminactions.php?action=getProjects'})
            .success(function(data,status){
                $scope.projectdata=data;
                console.log(data);
            }).error(function(data,status){console.log(data)});
    }

    $scope.getCompleteTaskDetails= function(){
        $http({method: 'GET', url:srcurl+'/adminactions.php?action=getCompleteTasks'})
            .success(function(data,status){
                $scope.taskdata=data;
                console.log(data);
            }).error(function(data,status){console.log(data)});
    }

    // add/update operations


    $scope.addnewClient= function(newclient){
        $http({method:'GET', url: srcurl+'/adminactions.php?action=addclient&clientname='+newclient})
        $scope.newclient='';
        $scope.getAllClients();

    }
    $scope.selectThisClient= function(client){
        $scope.selectedClient=client;

    }
    $scope.deleteClient= function(client){
        if(confirm('Are u sure to delete this item?')){
            $http({method:'GET', url: srcurl+'/adminactions.php?action=deleteClient&clientid='+client.clientid})
                .success(function(data,status){
                    alert('deleted!');
                    window.location.href='clients.html';
                }).error(function(data,status){

                })
        }
    }
    $scope.updateClient= function(clientdata){
        $http({method:'GET', url: srcurl+'/adminactions.php?action=updateclient&clientid='+clientdata.clientid+'&clientname='+clientdata.clientname})
        var modal=$(this);
        modal.trigger('reveal:close')
    }
    $scope.updateprojectwithClient= function(projectid,clientid){
        $http({method:'GET', url: srcurl+'/adminactions.php?action=updateproject&projectid='+projectid+'&clientid='+clientid})
            .success(function(data,status){
                //$scope.getAllProjects();
                window.location.href='projects.html';
            }).error(function(data,status){

            });
    }


    //registeration

    $scope.userselecter= function(user){
        $scope.selecteduser=user;
    }

    $scope.registeruser = function(user) {

            $http({method: 'POST',url: srcurl+'/registerusers.php', data:user})
                .success(function(data,status){
                    window.location.href='adminhome.html';

                }).error(function(data,status){})
       // }
    };
    $scope.updateuser= function(user){

        $http({method: 'POST',url: srcurl+'/updateusers.php', data:user})
            .success(function(data,status){

                alert(data.message);
                if(data.status==1){
                    window.location.href='adminhome.html';
                }

            }).error(function(data,status){})
    }

    $scope.goback= function(){
        $scope.userview=0;

    }

    $scope.selecttaskbyuser= function(user){
        $scope.userview=1;
        $scope.userselected=user;
        $scope.findhistoricdata(user.userid)
    }

    $scope.selectTaskbyProject= function(task){
        $scope.userview=1;
        $scope.projectselected=task;

    }
    $scope.selectProjectByClients=function(clientid){
        $scope.userview=1;
        $scope.clientselected=clientid;
    }



    $scope.findhistoricdata=function(userid){

        $scope.getTaskByUser(userid);

        var today = new Date();
        var datestarter= today.format("yyyy-m")+'-01';
        var datetoday = today.format("yyyy-m-d");
        $scope.datechosenfrom=datestarter;
        $scope.datechosento=datetoday;

        $http({method: 'GET',url: 'asana/user/useractions.php?action=findhistoricdata&userid='+userid})
            .success(function(data,status){
                $scope.filterdhistory=data;
                $scope.filterdataByproject=data;
                $scope.filterdhistory_backup=data;

                var timetake=0;
                angular.forEach(data, function(value, key) {
                    timetake=parseInt(timetake)+parseInt(value.totaltime);
                })
                $scope.timeworked=timetake;
            }).error(function(data,status){

            });
    }

    $scope.findProjectTasksByProject= function(){
       // console.log($scope.projectselected);
        if($scope.projectselected=='self'){
           // $scope.getCompleteTaskDetails();
            $scope.getProjctTasks('');
            // $scope.syncProjectTasks($scope.paramValue);
        }
        else{
            $scope.getProjctTasks($scope.projectselected);
           // $scope.syncProjectTasks($scope.projectselected);
        }
    }

    $scope.findProjectByClients= function(){
        $http({method: 'GET', url:'asana/admin/adminactions.php?action=findclientproject&clientid='+$scope.clientselected})
            .success(function(data,status){
                $scope.clientdetails=data;
                console.log(data);
            }).error(function(data,status){

            })

    }

    $scope.getCompleteTaskDetails= function(){
        $http({method: 'GET', url:'asana/admin/adminactions.php?action=getworkspacetasks'})
            .success(function(data,status){
                $scope.taskdata=data;
                console.log(data);
            }).error(function(data,status){console.log(data)});

    }
    $scope.getProjctTasks= function(projectid){


        //$http({method:'GET',url:'asana/admin/adminactions.php?action=getprojecttasks&projectid='+projectid})
        $http({method:'GET',url:'asana/admin/adminactions.php?action=projecttiming&projectid='+projectid})
            .success(function(data,status){
                $scope.projecttasks=data;
                $scope.projecttasks_backup=data;
                var timetake=0;
                angular.forEach(data.tasks, function(value, key) {
                    timetake=parseInt(timetake)+parseInt(value.totaltime);
                })
                $scope.timeworked=timetake;
              //  console.log($scope.projecttasks);
            }).error(function(data,status){

            });
    }

    //sync

    $scope.syncProjectTasks= function(projectid){
        $http({method:'GET', url:'asana/admin/adminactions.php?action=syncprojecttasks&projectid='+projectid})
            .success(function(data,status){
                $scope.getProjctTasks(projectid)
            })
    }

    $scope.syncUsers= function(){
        $scope.loaderstat=1
        $http({method: 'GET', url:srcurl+'/adminactions.php?action=syncallusers'})
            .success(function(data,status){
                $scope.loaderstat=0;
            }).error(function(data,status){

            })
    }
    $scope.syncWorkSpaceProjects= function(){
        $scope.loaderstat=1;
        $http({method:'GET', url:srcurl+'/adminactions.php?action=syncworkspace'})
            .success(function(data,status){
                $http({method: 'GET', url:srcurl+'/adminactions.php?action=syncprojects'})
                    .success(function(data,status){
                        $scope.getAllProjects();
                        $scope.loaderstat=0;
                    })
            }).error(function(data,status){
                console.log(data);
            })
    }
    $scope.syncProjects= function(){
        $scope.loaderstat=1;
        $http({method: 'GET', url:srcurl+'/adminactions.php?action=syncprojects'}).success(function(data,status){
            $scope.loaderstat=0;
        })
    }





    //selectors
    $scope.getTaskByUser= function (userid){

        $http({method: 'GET',url: 'asana/user/useractions.php?action=gettaskdata&userid='+userid})
            .success(function (data,status){
                $scope.taskinfo=data;
                var projectlist=[];
                projectlist.push({id:1,name:'--All Projects--'})
                projectlist.push({id:0,name:'no projects'})
                angular.forEach(data, function(value, key){
                    if(value.project.id){
                        var unique= true;
                        angular.forEach(projectlist, function(valuex,key){
                            if(valuex.id==value.project.id){
                                unique=false;
                            }
                        })
                        if(unique){
                            projectlist.push(value.project);
                        }
                    }
                })

                $scope.projectbyuser=projectlist;
                $scope.selectedproject=projectlist['0'];
            })
            .error(function (data,status){

            })
    }

    $scope.selectbyProject= function(selectedproject){
        var array = [];

        if(selectedproject.id!=1) {
            angular.forEach($scope.filterdhistory_backup, function (value, key) {
                if (selectedproject.id == value.project.id) {
                    array.push(value);

                    //delete $scope.filterdhistory[key]
                }
            });
            $scope.filterdataByproject=angular.copy(array);

        }
        else{
            $scope.filterdataByproject=angular.copy($scope.filterdhistory_backup);
            //$scope.findhistoricdata();
        }

        //$scope.filterdhistory = array;
        //console.log('from:'+$scope.datechosenfrom+'&to'+$scope.datechosento)
        $scope.searchByDate($scope.datechosenfrom,$scope.datechosento)
    }



    $scope.searchByDate= function(datechosenfrom,datechosento){

        if(!datechosenfrom&&!datechosento){
            alert('select at least one date!')
        }
        else if(!datechosenfrom&&datechosento){
            alert('select starting date')
        }
        else if(datechosento&&(datechosento<datechosenfrom)){
            alert('please select valied date range')
        }
        else {
            $scope.datechosenfrom=datechosenfrom;
            $scope.datechosento=datechosento;
            //$scope.selectbyProject($scope.selectedproject)
            $scope.filterdhistory=angular.copy($scope.filterdataByproject);
            var array = [];
            if(!datechosento){

                angular.forEach($scope.filterdhistory, function (value, key) {
                    if (datechosenfrom == value.date_worked) {
                        array.push(value);
                    }
                });
            }
            else{
                angular.forEach($scope.filterdhistory, function (value, key) {
                    if (datechosenfrom <= value.date_worked && value.date_worked <= datechosento) {
                        array.push(value);
                    }
                });
            }
            $scope.filterdhistory = array;
            var timetake=0;
            angular.forEach(array, function(value, key) {
                timetake=parseInt(timetake)+parseInt(value.totaltime);
            })
            $scope.timeworked=timetake;

        }
    }


    $scope.selectbyUser= function(userselected){
        var array = [];
        $scope.projecttasks=angular.copy($scope.projecttasks_backup);
        angular.forEach($scope.projecttasks_backup.tasks, function (value, key) {
            if (userselected.userid == value.assigned_user.id) {
                array.push(value)
                //delete $scope.filterdhistory[key]
            }
        });
        var timetake=0;
        angular.forEach(array, function(value, key) {
            timetake=parseInt(timetake)+parseInt(value.totaltime);
        })
        $scope.timeworked=timetake;
        $scope.projecttasks.tasks=array;
        console.log($scope.projecttasks);
    }



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

});