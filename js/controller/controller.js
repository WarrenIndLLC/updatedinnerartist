angular.module('mainApp')
	.controller('mainController', ['$scope','$location', '$http','$rootScope','httpService','$routeParams',function($scope, $location, $http,$rootScope,httpService,$routeParams) {
		console.info("mainController");
        $rootScope.baseurl = 'innerartist/';
		$scope.formData = {};
        $scope.vendorFormdata = {};
        $rootScope.productCart = [];
        var newDate = new Date();
        $scope.year = newDate.getFullYear();
        $rootScope.authUser = false;
        
        httpService._get($rootScope.baseurl+'authuser',{}).then(function(response){
            if(response.status){
                $rootScope.authUser = response.data;
            }
            $scope.getProdctCart();
            $scope.cartResponse();
        });
        $scope.Logout = function(){
            httpService._post($rootScope.baseurl+'auth/logout').then(function(cartResponse){
                $rootScope.authUser = null;
                window.location.href ="#/";
            });
        }


        setTimeout(function(){ 
            $scope.searchResult = function(search){
                /*alert(search);*/
                $searchData = {};
                $scope.formData.search = search;
                console.log($scope.formData);
                httpService._post($rootScope.baseurl+'search',$scope.formData).then(function(data){
                    $("#autosuggestion").show();
                    $('#autosuggestion').html(' ');
                    var url = $('#autosuggestion');
                    var searchData = data;
                    console.log(searchData);
                    if(data!=null){
                        for (var i = 0; i < searchData.length; i++) {
                            $('<a href="#/products/'+searchData[i].id+'"><div class="suggestion-inner"><span class="pac-item-query suggestiontext">'+ searchData[i].title+'</span></div></a>').appendTo(url);
                        };
                        search = {};
                    }else{
                        $("#autosuggestion").hide();        
                    }   
                });
            } 
        }, 50);
    

        $scope.getProductCartLocal = function(){
            $rootScope.total = 0;
            $rootScope.shipping_fee = 0;
            $rootScope.net_total = 0;
            var oldCarts = localStorage.getItem("innerartist_product");
            console.log(oldCarts);
            if(oldCarts!=undefined){
                var response = [];
                var responseData = JSON.parse(oldCarts);
                angular.forEach(responseData, function(details) {  
                    if(details!=null){
                        var row = JSON.parse(details);
                        if(parseInt(row.quantity)<1){
                            row.quantity= 1;
                        }
                        /*
                            Price Calculate
                        */
                        var total_price = parseFloat(row.regular_price) * parseInt(row.quantity);
                        console.log(total_price);
                        var price = parseFloat(row.regular_price);
                        row.regular_price = price.toFixed(2);

                        /*
                        Shipping Fee
                        */
                        var shipping = parseFloat(row.flat_rate)  * parseInt(row.quantity);
                        $rootScope.shipping_fee = parseFloat($rootScope.shipping_fee) + parseFloat(shipping);

                        var flat_rate = parseFloat(row.flat_rate);
                        row.flat_rate = flat_rate.toFixed(2);
                         /*
                            Total Calculate
                        */
                        var total = total_price;// + parseFloat(row.flat_rate);
                        row.total = parseFloat(total).toFixed(2);
                        $rootScope.total = parseFloat(row.total) + parseFloat($rootScope.total);

                        response.push(row);
                    }
                });
                /*console.log(response.length);    */
                $rootScope.productCart = response;
                $rootScope.localCart = response;
                var net_total =  $rootScope.total + $rootScope.shipping_fee;
                console.log($rootScope.total);
                $rootScope.total =  parseFloat($rootScope.total).toFixed(2)
                $rootScope.shipping_fee = parseFloat($rootScope.shipping_fee).toFixed(2);
                $rootScope.net_total = parseFloat(net_total).toFixed(2);
            }
        }

        $scope.cartResponse = function(){
            httpService._get($rootScope.baseurl+'cart').then(function(cartResponse){
                $rootScope.cartItem = cartResponse;
                angular.forEach($rootScope.cartItem, function(row) {   
                    row.size = row.width+"x"+row.height;
                });
            });
        }

        $scope.getProdctCart = function(){
            $scope.formData = {};
            $rootScope.total = 0;
            $rootScope.shipping_fee = 0;
            $rootScope.net_total = 0;
            if($rootScope.authUser){
                $scope.getProductCartLocal();
                if($rootScope.localCart==undefined)
                    $rootScope.localCart = [];
                $scope.cartForm =  {'cart':$rootScope.localCart};
                console.log($scope.cartForm);

                httpService._post($rootScope.baseurl+'productcart',$scope.cartForm).then(function(response){
                    localStorage.setItem('innerartist_product',[]);
                    localStorage.removeItem('innerartist_product');
                    var art_price = 0;
                    angular.forEach(response.data, function(row) {
                        var percent = parseFloat(row.art_id.price) * parseFloat(row.regular_price);
                        var final_percentage =  percent/100;
                        row.price_with_artwork = parseFloat(final_percentage);

                        if(parseInt(row.quantity)<1){
                            row.quantity= 1;
                        }
                        /*
                            Price Calculate
                        */
                        $rootScope.regular_price = row.regular;
                        var total_price = parseFloat(row.regular_price) * parseInt(row.quantity);
                        var price = parseFloat(row.regular_price);
                        row.regular_price = price.toFixed(2);

                        /*
                        Shipping Fee
                        */
                        var shipping = parseFloat(row.flat_rate)  * parseInt(row.quantity);
                        $rootScope.shipping_fee = parseFloat($rootScope.shipping_fee) + parseFloat(shipping);

                        var flat_rate = parseFloat(row.flat_rate);
                        row.flat_rate = flat_rate.toFixed(2);
                         /*
                            Total Calculate
                        */
                        var total = total_price;// + parseFloat(row.flat_rate);
                        row.total = parseFloat(total).toFixed(2);
                        $rootScope.total = parseFloat(row.total) + parseFloat($rootScope.total) + row.price_with_artwork;

                        
                        art_price = art_price + row.price_with_artwork;
                        $rootScope.artwork_price = art_price;
                    });

                    $rootScope.productCart = response.data;

                    var net_total =  $rootScope.total + $rootScope.shipping_fee;
                    $rootScope.total =  parseFloat($rootScope.total).toFixed(2)
                    $rootScope.shipping_fee = parseFloat($rootScope.shipping_fee).toFixed(2);
                    $rootScope.net_total = parseFloat(net_total).toFixed(2);                
                });
            }else{
                $scope.getProductCartLocal();
            }
        }

        

        $scope.redirectToProduct = function(productID){
            $.fancybox.close();
            if(productID!=undefined && productID!=0){
                $location.path("/products/"+productID);
            }else{
                $location.path("/products");
            }
        }

        $scope.viewArtDetails = function(row){
            $scope.artDetails = row;
        }

        $scope.removeFromCart = function(cartID){
            $.fancybox.close();
            $rootScope.loader = true;
            httpService._post($rootScope.baseurl+'removeArtCart',{'cartID':cartID}).then(function(response){
                $rootScope.loader = false;
                if(response.status){
                    $scope.message = "Thank You for contacting us!";
                    $rootScope.cartItem = response.data;
                    angular.forEach($rootScope.cartItem, function(row) {   
                        row.size = row.width+"x"+row.height;
                    });
                }else{
                    $scope.errorMsg = "There is an error,Please try again.";
                }
            });
        }

        $scope.openImage = function(row){
            $scope.image = row;
        }

        $scope.addVendor = function(){
            console.log($scope.vendorFormdata);
            $scope.vendorFormdata.user_id = $rootScope.authUser.id;
            console.log($scope.vendorFormdata);
            httpService._post($rootScope.baseurl+'vendor/add',$scope.vendorFormdata).then(function(response){
                if(response.status){
                    $scope.message = "Vendor Added Successfully";
                    $scope.vendorFormdata = {};
                }else{
                    $scope.errorMsg = "Error While submitting Please try again.";
                }
            });
        }
        
        
        $scope.markAsFavorites = function(artID,status){
            $scope.artLoader = true;
            httpService._post($rootScope.baseurl+'favartwork',{'art_id':artID,'status':status})
            .then(function(response){
                $scope.artLoader = false;
                if(response.success){
                    $scope.artworkDetails.is_liked = response.status;
                }               
            });
        }

        $scope.addCart = function(artID){
            $scope.artLoader = true;
            httpService._post($rootScope.baseurl+'addcart',{'art_id':artID})
            .then(function(response){
                $scope.artLoader = false;
                $scope.artworkDetails.addedTOCart = response.status;
                $rootScope.cartItem = response.data;
                angular.forEach($rootScope.cartItem, function(row) {   
                    row.size = row.width+"x"+row.height;
                });
            });
        }

        $scope.openArtWork = function(row){
            $scope.artworkDetails = row;
        }

        $scope.redirectToDashboard = function(){
            location.href = $rootScope.baseurl+"#/dashboard/albums/";
        }

}]).controller('homeCtrl', ['$scope','$location', '$http','$rootScope','httpService','$routeParams',function($scope, $location, $http,$rootScope,httpService,$routeParams) {
        console.info("homeCtrl");

        $scope.formData = {};

        httpService._get($rootScope.baseurl+'randomuser',{})
        .then(function(response){
            $scope.randomUser = response;
            $rootScope.loader = false;
        });

        httpService._get($rootScope.baseurl+'home',{}).then(function(response){
            $scope.blogs = response;
            $rootScope.loader = false;
        });

        httpService._get($rootScope.baseurl+'latestartworks',{})
        .then(function(response){
            $scope.artwork = response;
            $rootScope.loader = false;
        });

}]).controller('contactCtrl', ['$scope','$location', '$http','$rootScope','httpService','$routeParams',function($scope, $location, $http,$rootScope,httpService,$routeParams) {
        console.info("contactCtrl");
        $scope.formData = {};


        $scope.contactUs = function(){
            $rootScope.loader = true;
            httpService._post($rootScope.baseurl+'contact',$scope.formData)
            .then(function(response){
                $rootScope.loader = false;
                if(response.status){                    
                    $scope.message = "Thank You for contacting us!";
                }else{
                    $scope.errorMsg = "Error While submitting Please try again.";
                }
                $scope.formData = {};
            });
        }

}]).controller('artworkCtrl', ['$scope','$location', '$http','$rootScope','httpService','$routeParams',function($scope, $location, $http,$rootScope,httpService,$routeParams) {
        console.info("artworkCtrl");
        $scope.formData = {};
        $.fancybox.close();
        $scope.size='';
        $rootScope.loader = true;
        if($routeParams.userID!=undefined){
            httpService._post($rootScope.baseurl+'userartwork',{'user_id' : $routeParams.userID}).then(function(response){
                $scope.userArtwork = response;
                $rootScope.loader = false;
            });

            $scope.open = function(row){
                $scope.data = row;
            }
        }else{
            $rootScope.loader = true;
            httpService._get($rootScope.baseurl+'user/artwork').then(function(response){
                $scope.artworks = response;
                angular.forEach($scope.artworks, function(row) {   
                    row.size = row.width+"x"+row.height;
                });
                $rootScope.loader = false;
            });    
        }
        /*httpService._get($rootScope.baseurl+'user/artwork').then(function(response){
            $scope.artworks = response;
            angular.forEach($scope.artworks, function(row) {   
                row.size = row.width+"x"+row.height;
            });
            $rootScope.loader = false;
        });   */     

}]).controller('productCtrl', ['$scope','$location', '$http','$rootScope','httpService','$routeParams','FancyboxService',function($scope, $location, $http,$rootScope,httpService,$routeParams,FancyboxService) {
        console.info("productCtrl");
        $("div.mk-fullscreen-search-overlay").removeClass("mk-fullscreen-search-overlay-show");
        $scope.formData = {};
        $rootScope.loader = true;
        
        $scope.getInformaiton = function(){
            /*alert("pradosh");*/
        }

        $scope.getInformaiton();

        if($routeParams.productID!=undefined){
            
            $scope.selectedArt = {};

            $scope.selectArts = function(row){
                console.log(row);
                $scope.errorMsg = false;
                $scope.selectedArt =row;
                console.log($scope.selectedArt);
                console.log($scope.regular_price);
                $.fancybox.close();
                /*$image.cropper('destroy');*/
                $scope.image = $('#cropper_image');
                $scope.image.attr('src',"innerartist/uploads/"+row.user_id+"/"+row.art_id+"/"+row.file_name);
                FancyboxService.open('div.cropping');
                if($scope.image != "innerartist/uploads/"+row.user_id+"/"+row.art_id+"/"+row.file_name){
                    $scope.image.cropper('destroy');
                }
                setTimeout(function(){
                    $scope.image.cropper({
                        aspectRatio: 1,
                        viewMode: 1,
                        minCropBoxWidth: 200,
                        minCropBoxHeight: 200,
                        autoCropArea: 0.5,
                        restore: true,
                        guides: false,
                        highlight: false,
                        toggleDragModeOnDblclick: false,
                        built:function(){
                          $scope.image.cropper("setCropBoxData", { width: 200, height: 200 });
                        }
                    });
                },200);
            }

            $scope.cropArt = function(da){
                $scope.url=$scope.image.cropper('getCroppedCanvas',{ width: 200, height: 200 }).toDataURL();
                $scope.image.cropper('getCroppedCanvas', { width: 200, height: 200 }).toBlob(function (blob) {
                    console.log($scope.image);
                  /*$("#bgpreloaders").show();
                  var fd = new FormData();
                  fd.append('file', blob);
                  fd.append('type','profile');*/
                 
                  /*$.ajax({
                     url:base_url+'profile/uploadImage',
                     data:fd,
                     processData:false,
                     contentType:false,
                     type:'POST',
                     dataType:'json',
                     success:function(data){

                        $("#updatePhotoMsg").show();
                        $("#updatePhotoMsg").html("Profile Pic updated, Please wait.");
                        $("#bgpreloaders").hide();
                        window.location.reload();
                        $modal.modal('show');
                     }
                  });*/
                });
            }     


            httpService._post($rootScope.baseurl+'singleproduct',{'id' : $routeParams.productID})
            .then(function(response){
                $scope.singleProduct = response.product;
                $scope.singleProduct.quantity = 1;
                $scope.relatedProducts = response.related;
                $rootScope.loader = false;
                if($scope.singleProduct.is_canvas_product == 1){
                    $scope.showPagename = "canvasProduct"; 
                    httpService._get($rootScope.baseurl+'cart').then(function(response){
                        $scope.artworks = response;
                    });
                }else{
                   $scope.showPagename = "simpleProduct"; 
                   httpService._get($rootScope.baseurl+'user/artwork').then(function(response){
                        $scope.artworks = response;
                    });
                }
                if(!$rootScope.authuser){
                    var oldCarts = localStorage.getItem("innerartist_product");
                    if(oldCarts!=undefined){
                        var responseData = JSON.parse(oldCarts);
                        angular.forEach(responseData, function(details) {  
                            if(details!=null){
                                var row = JSON.parse(details);
                                if(row.id==$scope.singleProduct.id){
                                    $scope.singleProduct.addedTOCart = true;
                                }
                            }
                        });
                    }
                }
                 console.log($scope.singleProduct.addedTOCart);
            });

            $scope.productAddToCart = function(productRow){
                if($scope.selectedArt.art_id!=undefined){
                    $scope.errorMsg = false;
                    $rootScope.loader = true;
                    if($rootScope.productCart.indexOf(productRow)>-1){
                        $scope.singleProduct.addedTOCart = true;

                    }

                    if($rootScope.authUser){
                        console.log($rootScope.productCart);
                        $scope.productData = {
                            'product_id':productRow.id,
                            'art_id' : $scope.selectedArt.art_id
                        }
                        httpService._post($rootScope.baseurl+'productaddcart',$scope.productData).then(function(response){
                            $scope.singleProduct.addedTOCart = response.status;
                            $rootScope.loader = false;
                            $scope.getProdctCart();
                        });

                    }else{
                        productRow.art_id = $scope.selectedArt;
                        $scope.singleProduct.addedTOCart = true;
                        var value=[];
                        var oldCarts = JSON.parse(localStorage.getItem("innerartist_product"));
                        if(oldCarts!=undefined){
                            angular.forEach(oldCarts, function(row) {  
                                if(row!=null){
                                    var details = JSON.parse(row);
                                    value[details.id] = row;
                                }
                            });
                        }
                        value[productRow.id] = JSON.stringify(productRow);                    
                        localStorage.setItem('innerartist_product',JSON.stringify(value));
                        $rootScope.loader = false;
                        $scope.getProdctCart();
                    }
                }else{
                    $scope.errorMsg = "Please select art first";
                }
            }

            $scope.viewArtWorkDetails = function(row){
                $scope.artWorkDetails = row;
            }

            $scope.viewProductDetaisl = function(row){
                $scope.prodctDetails = row;
            }

            $scope.addCart = function(artID){
                $scope.artLoader = true;
                httpService._post($rootScope.baseurl+'addcart',{'art_id':artID})
                .then(function(response){
                    $scope.artLoader = false;
                    $scope.artWorkDetails.addedTOCart = response.status;                
                });
            }
        }else{
            httpService._get($rootScope.baseurl+'user/product').then(function(response){
                $rootScope.loader = false;
                $scope.products = response.product;
                $scope.category = response.category;                
            }); 

            $scope.filters = {
                'category_id' : '',
                'regular_price' : 'regular_price'
            };
            $scope.priceFilterExp = true;
            $scope.size='DESC';

            $scope.changeCat = function(){
                $scope.filters.category_id = $scope.category_id;    
            }

            $scope.changePrice = function(){
                if($scope.size=='DESC'){
                    $scope.priceFilterExp = true;    
                }else{
                    $scope.priceFilterExp = false;    
                }                    
            }
        }
}])

.controller('cmsCtrl', ['$scope','$location', '$http','$rootScope','httpService','$routeParams',function($scope, $location, $http,$rootScope,httpService,$routeParams) {
        console.info("cmsCtrl");
        $scope.formData = {};

        httpService._get($rootScope.baseurl+'faq',{}).then(function(response){
            $scope.faqs = response;
            $rootScope.loader = false;
        });

        $scope.supportTicket = function(){
            $rootScope.loader = true;
            httpService._post($rootScope.baseurl+'support-ticket',$scope.formData)
            .then(function(response){
                $rootScope.loader = false;
                if(response.status){                    
                    //$scope.message = "Thank You for contacting us!";
                }else{
                    $scope.errorMsg = "Error While submitting Please try again.";
                }
                $scope.formData = {};
            });
        }

}])

.controller('cartCtrl', ['$scope','$location', '$http','$rootScope','httpService','$routeParams',function($scope, $location, $http,$rootScope,httpService,$routeParams) {
        console.info("cartCtrl");
        $scope.formData = {};
        $scope.promoCode ={};
        $scope.placeorder = false;

        $scope.updatePrice = function(){
            $rootScope.total = 0;
            $rootScope.shipping_fee = 0;
            $rootScope.net_total = 0;
            var value = [];
            
            angular.forEach($rootScope.productCart, function(row) {  
                var percent = parseFloat(row.art_id.price) * parseFloat(row.regular_price);
                var final_percentage =  percent/100;
                row.price_with_artwork = parseFloat(final_percentage);
                
                value[row.id] = JSON.stringify(row);
                if(parseInt(row.quantity)<1){
                    row.quantity= 1;
                }
                /*
                    Price Calculate
                */
                var total_price = parseFloat(row.price) * parseInt(row.quantity);
                var price = parseFloat(row.price);
                row.price = price.toFixed(2);

                /*
                Shipping Fee
                */
                var shipping = parseFloat(row.flat_rate)  * parseInt(row.quantity);
                $rootScope.shipping_fee = parseFloat($rootScope.shipping_fee) + parseFloat(shipping);

                var flat_rate = parseFloat(row.flat_rate);
                row.flat_rate = flat_rate.toFixed(2);
                 /*
                    Total Calculate
                */
                var total = total_price;// + parseFloat(row.flat_rate);
                row.total = parseFloat(total).toFixed(2);
                $rootScope.total = parseFloat(row.total) + parseFloat($rootScope.total);
            });
            if(!$rootScope.authUser){
                localStorage.setItem('innerartist_product',JSON.stringify(value));
            }
            var net_total =  $rootScope.total + $rootScope.shipping_fee;
            $rootScope.total =  $rootScope.total.toFixed(2)
            $rootScope.shipping_fee = parseFloat($rootScope.shipping_fee).toFixed(2);
            $rootScope.net_total = net_total.toFixed(2);
        }

         $scope.doApplyCoupon = function(){
            console.log($scope.promoCode);
            $rootScope.loader = true;
            httpService._post($rootScope.baseurl+'apply/promo-code',$scope.promoCode).then(function(response){
                if(response.data){
                    $scope.msg = "Code Apply Successfully";
                    $scope.netPrice = $rootScope.net_total-response.data[0].total_cart_discount;
                    $rootScope.net_total = $scope.netPrice;
                    $rootScope.loader = false;
                }else{
                    $scope.errorMsg = "This Promo Code is not Valid";
                    $rootScope.loader = false;
                }  
            });
        }

        $scope.updateCart = function(){
            $rootScope.loader = true;
            $scope.formData.product = $scope.productCart;
            httpService._post($rootScope.baseurl+'place/order',$scope.formData).then(function(response){
                if(response.success){
                    $rootScope.loader = false;
                }  
            });
        }

        $scope.doMinus = function(rows){
            rows.quantity = rows.quantity-1;
            var price = parseFloat(rows.price) * parseInt(rows.quantity);
            rows.total = price.toFixed(2);
            var index = $rootScope.productCart.indexOf(rows);
            $rootScope.productCart[index] = rows;
            $scope.updatePrice();
            $scope.updateCart();
        }

        $scope.doAddItem = function(rows){
            rows.quantity = parseInt(rows.quantity)+1;   
            var price = parseFloat(rows.price) * parseInt(rows.quantity);
            rows.total = price.toFixed(2);
            var index = $rootScope.productCart.indexOf(rows);
            $rootScope.productCart[index] = rows;
            $scope.updatePrice();
            $scope.updateCart();
        }

        $scope.deleteCartItem = function(row){
            console.log(row);
            var index = $rootScope.productCart.indexOf(row);
            $rootScope.productCart.splice(index, 1);
            httpService._post($rootScope.baseurl+'delete/cartproduct',{'cartID':row.id}).then(function(response){
                $scope.updatePrice();
                $scope.updateCart();
            });
        }

        /*********** Checkout ***********/
        $scope.billing = {};
        $scope.shipping = {};
        $scope.step = 1;

        $scope.confirmOrder = function(){
            if($rootScope.authUser){
                $rootScope.loader = true;
                $scope.formData.product = $rootScope.productCart;
                $scope.showPagename = "";
                httpService._post($rootScope.baseurl+'place/order',$scope.formData).then(function(response){
                    if(response.success){
                        $rootScope.loader = false;
                        $scope.placeorder = true;
                        httpService._get($rootScope.baseurl+'getbilling').then(function(response){
                            $scope.billing = response.billing;
                            $scope.shipping = response.shipping;
                            $scope.showPagename = "";
                        });
                    }  
                });
            }else{
                location.href = $rootScope.baseurl+"#/login/cart";
            }
            
        }
        $scope.billing = {};
        $scope.updateBilling = function(bill){
            $scope.billing = bill;
            $rootScope.loader = true;
            /*console.log(bill);*/
            httpService._post($rootScope.baseurl+'billing/add-update',$scope.billing).then(function(response){1
                if(response.success){
                    $rootScope.loader = false;
                    $scope.step = 2;
                    $scope.showPagename = "showShipping";   
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                }                                  
            });
        }

        $scope.updateShipping = function(ship){
            /*console.log($scope.billing);*/
            $scope.shipping = ship;
            /*console.log($scope.shipping);*/

            $rootScope.loader = true;           
            if($scope.shipping.first_name ==null){                
                $scope.shipping = $scope.billing;  
                $scope.shipping.order_note = ship.order_note;
            }
            httpService._post($rootScope.baseurl+'shipping/add-update',$scope.shipping).then(function(response){
                if(response.success){
                    $rootScope.loader = false;
                    $scope.step = 3
                    $scope.showPagename = "showReview";
                }
            });
        }

        $scope.showCheckoutBilling = function(){
            $scope.showPagename = "";
        }

        $scope.showCheckoutShipping = function(){
            $scope.showPagename = "showShipping";
        }

        $scope.showReviewPayment = function(){
            $scope.showPagename = "showReview";
        }
        $scope.doPayment = function(){
            $scope.paymentData = {};
            $scope.paymentData.art_price = $rootScope.artwork_price;
            $rootScope.loader = true;
            $scope.formData.order_note = $scope.shipping.order_note;
            $scope.formData.product = $rootScope.productCart;
            $scope.formData.total_amount= $rootScope.net_total;
            /*console.log($scope.formData);*/
            
            httpService._post($rootScope.baseurl+'order/payment',$scope.formData).then(function(response){
                console.log(response.data);
                $scope.paymentData.order_id = response.data;
                httpService._post($rootScope.baseurl+'order/release',$scope.paymentData).then(function(response){    
                });
                if(response.success){
                    $rootScope.productCart={};
                    localStorage.setItem('innerartist_product','');
                    localStorage.removeItem('innerartist_product');
                    $rootScope.loader = false;
                    $scope.message = response.message;
                    $rootScope.productCart = [];
                    $scope.cartForm =  {'cart':$rootScope.productCart};
                    httpService._post($rootScope.baseurl+'productcart',$scope.cartForm).then(function(response){                    
                        $rootScope.productCart = response;               
                    });
                }else{
                    $rootScope.loader = false;
                    $scope.errormessage = response.message;
                }
                $("html, body").animate({ scrollTop: 0 }, "slow");
            });
        }
}])

.controller('galleryCtrl', ['$scope','$location', '$http','$rootScope','httpService','$routeParams',function($scope, $location, $http,$rootScope,httpService,$routeParams) {
        console.info("galleryCtrl");
        $scope.formData = {};

        httpService._get($rootScope.baseurl+'gallery').then(function(response){
            $rootScope.loader = false;
            $scope.gallery = response.data;
            $scope.vendorDetail = response.user;
        });
        /*httpService._get($rootScope.baseurl+'user/artwork').then(function(response){
            $scope.artworks = response;
            angular.forEach($scope.artworks, function(row) {   
                row.size = row.width+"x"+row.height;
            });
            $rootScope.loader = false;
        });   */     

}])

.controller('favCtrl', ['$scope','$location', '$http','$rootScope','httpService','$routeParams',function($scope, $location, $http,$rootScope,httpService,$routeParams) {
        console.info("favCtrl");
        $scope.formData = {};
        $rootScope.loader = true;
        httpService._get($rootScope.baseurl+'artwork/favourite').then(function(response){
            $rootScope.loader = false;
            $scope.favArtwork = response;
        });

        $scope.viewFavArtDetails = function(row){
            $scope.favArtDetails = row;
        }
}]);


angular.module('mainApp').filter('searchFilter',function($filter) {
        return function(items,searchfilter) {
           var isSearchFilterEmpty = true;
            angular.forEach(searchfilter, function(searchstring) {   
                if(searchstring !=null && searchstring !=""){
                    isSearchFilterEmpty= false;
                }
            });
            if(!isSearchFilterEmpty){
                    var result = [];  
                    angular.forEach(items, function(item) {  
                        var isFound = false;
                         angular.forEach(item, function(term,key) {                         
                             if(term != null &&  !isFound){
                                 term = term.toString();
                                 term = term.toLowerCase();
                                    angular.forEach(searchfilter, function(searchstring) {      
                                        searchstring = searchstring.toLowerCase();
                                        if(searchstring !="" && term.indexOf(searchstring) !=-1 && !isFound){
                                           result.push(item);
                                            isFound = true;
                                        }
                                    });
                             }
                                });
                           });
                return result;
            }else{
             return items;
            }
    }
});