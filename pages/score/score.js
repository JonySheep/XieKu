// pages/score/sc.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    imgUrl: getApp().globalData.globalStaticUrl,
    score:0,
    shou:true,
    zhi:false,
    userinfo:'',
    shouzhi : true,  //收支  默认是收入
    income: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      var that = this;      
      var openid = wx.getStorageSync('openid');
      if( openid )
      {
          wx.request({
            url: 'https://www.swahouse.com/index.php/api/Users/getUserIntegralIncome',
            data: {
              openid: openid,
            },
            header: {
              'content-type': 'application/json'
            },
            success: function (res) {
                console.log(res.data.data)
                that.setData({
                  income: res.data.data.income,
                  expenditure: res.data.data.expenditure,
                  score : res.data.data.integral
                })
            }
          })
      }       
  },

  income:function(){
    var that =  this;
    that.setData({
        shouzhi :  that.data.shouzhi ? false : true
    })
  },

  // 立即兑换
  onRedeemFn(){
    wx.switchTab({
      url: '../logs/logs',
      fail: function (e) {
        console.log(e)
      }
    })
  },

  //跳转到活动详情页
  goactivedetailFn: function (e) {
    console.log(e.currentTarget.dataset.id)
    wx.navigateTo({
      url: '../activitydetails/activitydetails?id='+e.currentTarget.dataset.id,
    })
  },

  //跳转到商品详情页
  goproductdetailFn: function (e) {    
    console.log(e.currentTarget.dataset.id)
    if (e.currentTarget.dataset.id){
      wx.navigateTo({
        url: '../productdetails/productdetails?id=' + e.currentTarget.dataset.id,
      })
    }
    
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})