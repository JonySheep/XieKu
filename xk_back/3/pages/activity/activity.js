// pages/activity/activity.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
   
    obj1: {
      
      img1: "https://www.swahouse.com/image/welfare7.png",
      title1: "ONE 冷凝系列 -CLAS ONE 酷能 ",
      score1: 666,

    }
  },
  //我的活动
  myActivity:function()
  {   
      var that      = this 
      var userInfo  = wx.getStorageSync('userinfo')
      var userId    = userInfo.id 
      console.log(userId)
      wx.request({
          url   : 'https://www.swahouse.com/api/Activity/myActivity',
          data  : {
              users_id  : userId
          },
          success : function(rsf)
          {
              console.log(rsf)
          }
      })  
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

      //获取我的活动
      this.myActivity()
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