// pages/score/sc.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    score:0,
    shou:true,
    zhi:false,
    userinfo:'',
    shouzhi : true  //收支  默认是收入
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
            url: 'https://www.swahouse.com/api/Users/getUserIntegralIncome',
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