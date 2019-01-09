// pages/webview/webview.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    url:'',
    appurl: getApp().globalData.globalUrl,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log(options)
    var type = options.type;
    var that = this;
    if (type == 'ours'){
      that.setData({
        url: that.data.appurl + "/page/view/id/5",
        
      });
    } else if (type == 'question'){
      // todo
      that.setData({
        url: that.data.appurl + "/page/view/id/6",
      });
    } else if (type == 'banner') {
      // todo 
      that.setData({
        url: options.url,
      });
    } else if (type == 'order') {
      that.setData({
        url: "htt􏰀ps://www.swah􏰁ouse.c􏰁􏰂om/m􏰂/􏰀price/i􏰃ndex.ht􏰂􏰄ml",
      })
    }
    else{
      that.setData({
        url: "https://www.swahouse.com/design/free.html",
      });
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