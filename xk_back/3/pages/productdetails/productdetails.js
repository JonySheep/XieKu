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
     user_info: ''

   },

   /**
    * 生命周期函数--监听页面加载
    */
   onLoad: function(options) {
     var that = this;


     that.setData({
       i: 1,
     })
     //获取商品信息
     wx.request({

       url: that.data.appurl + 'goods/detail?id=' + options.id,
       header: {
         'content-type': 'application/json'
       },
       success: function(res) {
         var article = res.data.data['detail'];
         WxParse.wxParse('article', 'html', article, that);
         console.log(article)
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
   },
   join: function() {
     this.setData({
       hidden: false,
     })
   },
   exit: function() {
     this.setData({
       hidden: true,
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
   /**
    * 生命周期函数--监听页面初次渲染完成
    */
   onReady: function() {

   },

   /**
    * 生命周期函数--监听页面显示
    */
   onShow: function() {

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
   onShareAppMessage: function() {

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
   submit:function(){
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

     common.httpG('goods/checkStock', {
       number: self.data.i,
       id: self.data.goodsdetail.id,
     }, function (data) {
      console.log(data);
        //存储到storage中
        wx.setStorageSync('order', {
          goods: self.data.goodsdetail,
          number: self.data.i,
          address_id: 0,
          store_id: 0,
          pay_way: 1,
          send_way: 1,
        })
        wx.navigateTo({
          url: '../xiadan/xiadan',
        })

     })

   
   },

 })