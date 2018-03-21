/**
 * Created by netstager on 06/12/14.
 */

var app=angular.module('myApp', ['ngImgCrop']);
var srcurl='';
app.controller('ProfileController', function($scope,$http) {
        $scope.myImage='';
        $scope.myCroppedImage='';

        $scope.init= function(){
        if(localStorage.user) {
            $http({method: 'GET', url: srcurl+'asana/user/useractions.php?action=getuserdata&uid=' + localStorage.user})
                .success(function (data, status) {
                    $scope.response = data;
                })
        }
        else{
            window.location.href="login.html"
        }
    }

    $scope.updateuser= function(user){

        $http({method: 'POST',url: 'asana/admin/updateusers.php', data:user})
            .success(function(data,status){

                alert(data.message);
                if(data.status==1){
                    window.location.href='index.html';
                }

            }).error(function(data,status){})
    }


    var handleFileSelect=function(evt) {
        //console.log('changee!!!!!');
            var file=evt.currentTarget.files[0];
            var reader = new FileReader();
            reader.onload = function (evt) {
                $scope.$apply(function($scope){
                    $scope.myImage=evt.target.result;
                });
            };
            reader.readAsDataURL(file);
        };
        angular.element(document.querySelector('#fileInput')).on('change',handleFileSelect);


    $scope.logout= function(){
       // $scope.uid='';
        localStorage.user="";
        window.location.href="login.html";
        //$scope.status=-1;
        $scope.response=[];
        //$scope.taskinfo=[];
        //$scope.timeelapsed=0;
        //$scope.historytasks=[];
        //$scope.filterdhistory=[];
    }



    });