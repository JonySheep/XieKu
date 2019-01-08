// pages/activity/activity.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    keywords:'',
    sort: '0',
    asc: '1',
    id: '',
    categorydetail: ''
  },
  gotoInfoFn: function(e){
    wx.navigateTo({
      url: '../productdetails/productdetails?id=' + e.currentTarget.dataset.goodsid
    })
  },

  getkeyFn(e){
    console.log(e.detail.value)
    this.setData({
      keywords: e.detail.value
    })
    this.getgoodsList()
  },
  changeinfoFn(e){
    console.log(e.currentTarget.dataset.sort)

    this.setData({
      sort: e.currentTarget.dataset.sort
    })

    if (this.data.asc == 1){
      this.data.asc = 2
      this.setData({
        asc: this.data.asc
      })
      this.getgoodsList()
      return
    } 
    if (this.data.asc == 2) {
      this.data.asc = 1
      this.setData({
        asc: this.data.asc
      })
      this.getgoodsList()
      return
    } 
    
    
  },
  getgoodsList() {
    var that = this;
    //获取商品列表信息
    wx.request({

      url: 'https://www.swahouse.com/index.php/api/Brand/getGoods',
      data: {
        brand_id: this.data.id,
        keywords: this.data.keywords,
        sort: this.data.sort,
        asc: this.data.asc
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        that.setData({
          categorydetail: res.data.data
        });
      }
    });
  },



  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    that.setData({
      id: options.id
    })
    that.getgoodsList()
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

    app.globalData.isaddcar = true
    app.changecarNumFn()
   

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