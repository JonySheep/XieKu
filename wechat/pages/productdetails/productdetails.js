 var custShowToast = require('../../utils/custshowtoast'); //引入消息提醒暴露
 var d = 1;
 var app = getApp();
 var WxParse = require('../../wxParse/wxParse.js');
 var common = require("../../utils/util.js");
 Page({

   /**
    * 页面的初始数据
    */
   data: {
     appurl: getApp().globalData.globalUrl,
     imgUrl: getApp().globalData.globalStaticUrl,
     hiddenAdvertisement: true,
     hidden: true,
     chice: 0,
     i: 1,
     indicatorDots: true,
     autoplay: true,
     interval: 5000,
     duration: 1000,
     winHeight: 0,
     goodsdetail: {},
     uid: '',
     user_info: '',
     showaddcar: false,
     shounowbuy: false,
     shopcall: '',
     isshowmodel:false,
     shareId:'',
     typech: true,
   },
   moveFn(){
     
   },

   /**
    * 生命周期函数--监听页面加载
    */
   onLoad: function(options) {
     var that = this;
     var share_token = '';
     if (options.share_token){
       share_token = options.share_token;
     }
     that.setData({
       i: 1,
     })
     //获取商品信息
     wx.request({
       url: that.data.appurl + 'goods/detail?id=' + options.id + '&share_token=' + share_token ,
       header: {
         'content-type': 'application/json'
       },
       success: function(res) {
         var article = res.data.data['detail'];
         WxParse.wxParse('article', 'html', article, that);
         that.setData({
           goodsdetail: res.data.data
         });
       }
     });

     // 获取系统信息   
     wx.getSystemInfo({
       success: function(res) {
         that.setData({
           winWidth: res.windowWidth,
           winHeight: res.windowHeight
         });
       }
     });

     //获取客服电话
     wx.request({
       url: that.data.appurl + 'Settings/getMobile',
       header: {
         'content-type': 'application/json'
       },
       success: function (res) {
         that.setData({
           shopcall: res.data.data.mobile
         });
       }
     });
   },
   //切换详情规格
   changetype: function(){
      var self = this
      self.setData({
        typech: !self.data.typech
      })
   },
   join: function(e) {
    console.log(e)
     if (e.currentTarget.dataset.addtype == 1){
       this.setData({
         hidden: false,
         shounowbuy: false,
         showaddcar: true
       })
    }
     if (e.currentTarget.dataset.addtype == 2) {
       this.setData({
         hidden: false,
         shounowbuy: true,
         showaddcar: false
       })
     }


     
   },
   exit: function() {
     this.setData({
       hidden: true,
       showaddcar: false
     })
   },


   jian: function() {
     if (this.data.i <= 1) {
       this.setData({
         i: 1,
       })
     } else {
       this.setData({
         i: this.data.i - 1,
       });
     };
   },
   jia: function() {
     var self = this;
     this.setData({
       i: self.data.i + 1,
     });
     // this.setData({
     // d: i,
     // });

   },
   fenxiang: function() {
     this.setData({
       hiddenAdvertisement: true,
     })
   },
   fenxiangShow: function() {
     this.setData({
       hiddenAdvertisement: false,
     })
   },
   chooseAttr:function(e){
     var level = e.currentTarget.dataset.level;
     var attr = e.currentTarget.dataset.attr;
     this.data.goodsdetail['attribute'][level]['choose'] = attr;
     this.setData({
       goodsdetail: this.data.goodsdetail
     })
     console.log(this.data.goodsdetail)
   },
    //跳转到首页
   gohomeFn: function(){
     wx.switchTab({
       url: '../home/home'
     })

   },
  //  拨打电话
   makephoneFn: function(){
     wx.makePhoneCall({
       phoneNumber: this.data.shopcall,
     })
   },

  // 关闭购物车
   onCloseFn(){
     this.setData({
       hidden:true,
       i: 1,       
     })
   },
   
   /**
    * 生命周期函数--监听页面初次渲染完成
    */
   onReady: function() {

   },

   /**
    * 生命周期函数--监听页面显示
    */
   onShow: function() {
     wx.hideLoading()
     var self = this;
     var openid = wx.getStorageSync('openid');
     if (!openid) {
       wx.switchTab({
         url: '../center/center',
         fail: function (e) {
           console.log(e)
         }
       });
       return;
     }
   },

   /**
    * 生命周期函数--监听页面隐藏
    */
   onHide: function() {

   },

   /**
    * 生命周期函数--监听页面卸载
    */
   onUnload: function() {

   },

   /**
    * 页面相关事件处理函数--监听用户下拉动作
    */
   onPullDownRefresh: function() {

   },

   /**
    * 页面上拉触底事件的处理函数
    */
   onReachBottom: function() {

   },

   /**
    * 用户点击右上角分享
    */
   onShareAppMessage: function(res) {
        var that = this;
        that.setData({
          isshowmodel: false
        });
     var goods_path_params = 'pages/productdetails/productdetails?id=' + that.data.goodsdetail.id + '&share_token=' + that.data.shareId;
        if (res.from === 'button') {
          // 来自页面内转发按钮
          return {
            title: '斜厍',
            path: goods_path_params,
            success: function (res) {// 转发成功      
              console.log("转发成功:" + JSON.stringify(res));
              wx.showToast({
                title: '分享成功！'
              })
            }
          }
        }
   },

    // 唤起确认分享model框
   getShareUrl: function (ele) {
     var openid = wx.getStorageSync('openid');
     var goods_id = ele.target.dataset.id;
     var self = this;
     wx.request({
       url: self.data.appurl + 'Share/getShareUrl',
       header: {
         'content-type': 'application/json'
       },
       data: {
         openid: openid,
         goods_id: goods_id,
       },
       success: function (res) {
         if (res.data.code == 200) {
           self.setData({
             isshowmodel: true,
             shareId: res.data.data.share_token,
             goods_id: res.data.data.goods_id,
           });
         }
       }
     })
   },

   // 关闭分享窗口
   closeModelFn: function () {
     this.setData({
       isshowmodel: false
     });
   },
 
   addLike: function(e) {
     var self = this
     wx.request({
       url: app.globalData.globalUrl + "/goods/addLike",
       data: {
         uid: self.data.uid,
         id: e.currentTarget.dataset.id
       },
       method: "POST",
       header: {
         'content-type': 'application/x-www-form-urlencoded'
       },
       success: function(res) {
         custShowToast.showToast({
           title: res.data.data
         });
       }
     })

   },
   submit:function(e){
     console.log(e.currentTarget.dataset.sid)

    var self = this;
     //先登录
     var uinfo = common.getUserName();
     //检查是否有属性并选择
     if (this.data.goodsdetail.attribute){
       for (var i = 0; i < this.data.goodsdetail.attribute.length;i++){
         if (!this.data.goodsdetail.attribute[i]['choose'] || this.data.goodsdetail.attribute[i]['choose'] == 0){
           wx.showToast({
             title: '请选择' + this.data.goodsdetail.attribute[i]['name'],
             duration: 10000
           })
           return;
         }
       }
     }

      //检查产品是否存在

      wx.request({
        url: self.data.appurl + 'goods/checkStock',
        data: {
          number: self.data.i,
          id: self.data.goodsdetail.id,
        },
        header: {
          'content-type': 'application/json'
        },
        success: function(res){         
          if (e.currentTarget.dataset.sid == 1) {  //提交订单
            //生成订单
            wx.request({
              url: self.data.appurl + 'order/createOrder',
              data: {
                goodsID: self.data.goodsdetail.id,
                goods_number: self.data.i,
                openid: wx.getStorageSync('openid'),
                goods_attribute: '',
                share_token: self.data.goodsdetail.share_token
              },
              header: {
                'content-type': 'application/json'
              },
              success: function (data) {
                  if(data.data.code != 200){
                    wx.showToast({
                      title: data.data.err,
                      icon: 'none',
                      duration: 2000
                    })
                  }else{
                    wx.setStorageSync('order_id', data.data.data.id)
                    wx.setStorageSync('totomoney', data.data.data.intregral)
                    wx.setStorageSync('order_list', data.data.data.goods_list)
                    wx.setStorageSync('order_number', data.data.data.order_number)

                    self.setData({
                      hidden: true,
                      shounowbuy: false
                    })

                    wx.navigateTo({
                      url: '../xiadan/xiadan',
                    })
                  }
              }
            })           
          } else if (e.currentTarget.dataset.sid == 2) {  //加入购物车
            wx.request({
              url: self.data.appurl + 'cart/createCart',
              data: {
                openid: wx.getStorageSync('openid'),
                goods_id: self.data.goodsdetail.id,
                goods_num: self.data.i,
              },
              header: {
                'content-type': 'application/json'
              },
              success: function (data) {
                wx.showToast({
                  title: '添加成功'
                })

                self.setData({
                  hidden: true,
                  showaddcar: false
                })

              
                app.globalData.isaddcar = true
                app.changecarNumFn()
                

              }          
            })
          }
        }
      })
   }

 })