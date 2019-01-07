// pages/collect/collect.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
      activity : [],
  },
  //获取我的收藏
  myActivityCollect : function(usersId)
  {   
      var that      = this 
      wx.request({
          url   : 'https://www.swahouse.com/index.php/api/Activity/myActivityCollect',
          data  : {
              users_id : usersId
          },
          success : function(rsf)
          {
             // console.log(rsf)
              that.setData({
                  activity : rsf.data.data
              })
          }
      })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      var userInfo  = wx.getStorageSync('userinfo')
      var usersId   = userInfo.id 
      //获取收藏的活动
      this.myActivityCollect(usersId)
  },
  //活动详情
  activitydetails : function(e)
  {   
      var id = e.target.dataset.current
      console.log(id)
      wx.navigateTo({
          url: '../activitydetails/activitydetails?id='+id,
      })
  },
  //取消收藏
  cancelCollectActivity : function(e)
  {   
      var that        = this
      var openId      = wx.getStorageSync('openid')
      var activityId  = e.target.dataset.current
      wx.request({
          url   : 'https://www.swahouse.com/index.php/api/Activity/activityCollect',
          data  : {
              activity_id : activityId,
              open_id    : openId
          },
          success : function(rsf)
          {    
              var activityList = that.data.activity
              var data = rsf.data.data
              for(var i = 0; i < activityList.length; i ++ )
              {
                  if( (activityList[i].id == activityId) && data.rsf == '取消收藏成功' )
                  {
                      activityList[i].is_collect = 1
                  }else{
                      activityList[i].is_collect = 0
                  }
              }
              that.setData({
                  activity : activityList
              })
          }
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