// pages/newAdd/newAdd.js
var common = require("../../utils/util.js");
var app = getApp();
const imgurl = app.globalData.imgUrl;
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
		fullname: '',
		address: '', //收货地址
		is_default: 0,
		mobile: '',
		address_id: '',
		action: 'submitAddress',
		province : [], // 省 
		city 	 : [], //市
		range 	 : [],//区
		isHiddenSelect : true, //是否隐藏select 
		areaInfo : '', //地区信息
	},

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function (options) {
		//编辑地址
		var address_id = options.address_id;
		if (address_id !== undefined) {
			this.setData({
				action: 'editAddress',
			})
			this.getAddress(address_id)

		}
		this.getCity(1);
	},
	//编辑地址第一步
	getAddress: function (address_id) {
		var that = this
		var uinfo = common.getUserName();
		common.httpP('address/edit', {
      uid: uinfo.id,
			id: address_id
		}, function (data) {
			that.setData({
				address_id: data.data.id,
        fullname: data.data.fullname,
				mobile: data.data.mobile,
        address: data.data.address,
        is_default: data.data.is_default,
			})
		})
	},
	//编辑地址第二步：
	editAddress: function (event) {
		var data_address = event.detail.value;

		var uinfo = common.getUserName();

		var address_id = this.data.address_id;
		common.httpP('address/update', {
			id: address_id,
		    uid: uinfo.id,
		    fullname: data_address.fullname,
			mobile: data_address.mobile,
		    address: data_address.address,
		    is_default: data_address.is_default,

		}, function (data) {
			if (data.code == 1) {
				wx.navigateBack({

				})
			}
		})


	},
	//添加地址(提交表单)
	submitAddress: function (e) {
		var that      = this 
	    var userInfo  = wx.getStorageSync('userinfo')
	    var userId    = userInfo.id 
	    console.log(that.data.cityId)
		wx.request({
			url 	: 'https://www.swahouse.com/api/Users/addCity',
			data 	: {
				users_id	: userId,
    			users_name	: e.detail.value.users_name,
    			users_tel	: e.detail.value.users_tel,
    			city_id		: e.detail.value.city_id,
    			details		: e.detail.value.details,
    			postcode	: e.detail.value.postcode,
    			is_default  : that.data.is_default
			},
			success : function(rsf)
			{
				  console.log(rsf)
	              if( rsf.data.code === 400 )
	              {
	                  wx.showToast({
                          title : rsf.data.err,
                          icon: 'none',
                          duration: 2000
                      })
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
			url 	: 'https://www.swahouse.com/api/Users/getCity',
			data 	: {
				pid : pid
			},
			success : function(rsf)
			{
				console.log(rsf)
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
		console.log(this.data.cityId)
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