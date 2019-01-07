// pages/newAdd/newAdd.js
var common = require("../../utils/util.js");
var app = getApp();
const imgurl = app.globalData.imgUrl;
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
    appurl: getApp().globalData.globalUrl,
    users_name: '',
		address: '', //收货地址
		is_default: 0,
    users_tel: '',
		address_id: '',
		action: 'submitAddress',
		province : [], // 省 
		city 	 : [], //市
		range 	 : [],//区
		isHiddenSelect : true, //是否隐藏select 
		areaInfo : '', //地区信息
    postcode:'', //邮编
    details: '',
	},

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function (options) {
		//编辑地址
    var address_id = options.address_id;
    if(address_id){
      var self = this;
      var openid = wx.getStorageSync('openid');
      wx.request({
        url: app.globalData.globalUrl + 'address/edit',
        data: {
          openid: openid,
          id: address_id
        },
        method: 'POST',
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          if(res.data.code === 200){
            self.setData({
              users_name: res.data.data.fullname,
              users_tel: res.data.data.mobile,
              postcode: res.data.data.postal,
              is_default: res.data.data.is_default,
              address_id: address_id,
              areaInfo: res.data.data.address.split(',')[0],
              details: res.data.data.address.split(',')[1]
            })
          }else{
            wx.showToast({
              title: res.data.err,
            })
          }
        }
      })
    }
	},

	//添加地址(提交表单)
	submitAddress: function (e) {
		var that = this 
    var openid = wx.getStorageSync('openid');
    var address = that.data.areaInfo +','+ e.detail.value.details;
		wx.request({
      url: app.globalData.globalUrl+'address/add',
			data 	: {
          openid: openid,
          fullname: e.detail.value.users_name,
          mobile: e.detail.value.users_tel,
          address: address,
          postal: e.detail.value.postcode,
    			is_default: that.data.is_default,
          id: that.data.address_id
			},
      method:'POST',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
			success : function(rsf)
			{
	              if( rsf.data.code === 400 )
	              {
	                  wx.showToast({
                          title : rsf.data.err
                      })
	              }else{
                    wx.showToast({
                      title: '添加收货成功',
                      success:function(){
                        wx.navigateBack();
                      }
                    });

                }	
			}
		})
	},

	chooseAddr: function () {
		var that = this;
		wx.chooseLocation({
			success: function (res) {
				that.setData({
					address: res.address + res.name
				});

			},
		})
	},
	//获取省市区
	getCity : function(pid = 1 )
	{	
		var that = this 
		wx.request({
			url 	: 'https://www.swahouse.com/index.php/api/Users/getCity',
			data 	: {
				pid : pid
			},
			success : function(rsf)
			{
        that.setData({
					province 	: rsf.data.data,
					city 		: rsf.data.data[0].next,
					range 		: rsf.data.data[0].next[0].next
				})
			}
		})
	},
	//打开地址选择
	bindChange : function(e)
	{	
		var index = e.detail.value
		var provinceIndex = index[0]
		var cityIndex = index[1]
		var rangeIndex = index[2]
		this.setData({
			//province 	: this.data.province,
			city 		: this.data.province[provinceIndex].next,
			range 		: this.data.province[provinceIndex].next[cityIndex].next 
		})
		this.setAreaInfo(provinceIndex,cityIndex,rangeIndex)
	},
	//显示地区信息
	areaInfoClick : function()
	{	 	
		this.setData({
			isHiddenSelect : false
		})
		this.setAreaInfo(0,0,0)
	},
	//设置地区信息
	setAreaInfo:function(provinceIndex,cityIndex,rangeIndex)
	{	
		var province = this.data.province[provinceIndex]
		var city 	 = province.next[cityIndex]
		var range 	 = city.next[rangeIndex]
		var areaInfo = province.name+'-'+city.name+'-'+range.name
		this.setData({
			areaInfo : areaInfo,
			cityId   : range.id
		})
	},
	//取消选中的省市区
	cancelChange : function()
	{
		this.setData({
			isHiddenSelect : true,
			areaInfo 	   : '',
			cityId 		   : 0
		})
	},
	//确认选择
	affirmChage : function()
	{
		this.setData({
			isHiddenSelect : true
		})
	},
	//设置默认地址
	defaultAddress : function()
	{	
		var isDefault = this.data.is_default ? 0 : 1;
		this.setData({
			is_default : isDefault
		})
		console.log(this.data.is_default)
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
    this.getCity();
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