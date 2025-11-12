SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (1, 7, '首页顶部轮播图', 1, '/uploads/images/20210623170018c9a413210.jpeg', 1, '0', 0, 1, 0, 0, 50, 1624438839, NULL, 0);

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (2, 8, '首页中部广告', 1, '/uploads/images/20210623170129cad173742.png', 1, '1', 0, 1, 0, 0, 50, 1624438895, NULL, 0);

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (3, 10, '首页分类广告', 1, '/uploads/images/20210623170018d5e709551.jpeg', 1, '1', 0, 1, 0, 0, 50, 1624438952, NULL, 0);

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (4, 11, '分类顶部广告', 1, '/uploads/images/2021062317001844cf80052.jpeg', 2, '3', 0, 1, 0, 0, 50, 1624438988, NULL, 0);

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (5, 12, '热销榜单', 1, '/uploads/images/2021062317001897f1e7584.jpeg', 2, '4', 0, 1, 0, 0, 50, 1624439055, 1624439186, 0);

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (6, 13, '新品推荐广告', 1, '/uploads/images/20210623170018ea28c8944.jpeg', 2, '3', 0, 1, 0, 0, 50, 1624439094, NULL, 0);

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (7, 14, '商城资讯广告', 1, '/uploads/images/2021062317001844cf80052.jpeg', 0, '', 0, 1, 0, 0, 50, 1624439135, NULL, 0);

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (8, 20, '秒杀', 1, '/uploads/images/2021062317001844cf80052.jpeg', 2, '4', 0, 1, 0, 0, 50, 1624445886, NULL, 0);

INSERT INTO `ls_ad`(`id`, `pid`, `title`, `terminal`, `image`, `link_type`, `link`, `category_id`, `status`, `start_time`, `end_time`, `sort`, `create_time`, `update_time`, `del`) VALUES (9, 23, '首页', 2, '/uploads/images/202111021026477fb3a3512.png', 1, '0', 0, 1, 0, 0, 1, 1635819470, NULL, 0);

INSERT INTO `ls_article`(`id`, `cid`, `title`, `image`, `content`, `visit`, `likes`, `sort`, `is_notice`, `is_show`, `del`, `create_time`, `update_time`, `intro`) VALUES (1, 1, '我们不一样', 'uploads/images/2021062317001844cf80052.jpeg', '<p>我们不一样</p>', 0, 0, 10, 1, 1, 0, 1619602187, 1624439294, '就是不一样，就是不一样');

INSERT INTO `ls_article`(`id`, `cid`, `title`, `image`, `content`, `visit`, `likes`, `sort`, `is_notice`, `is_show`, `del`, `create_time`, `update_time`, `intro`) VALUES (2, 1, '常见问题', 'uploads/images/20210623170129cad173742.png', '<p>问：怎么退款？</p><p>答：发截屏发票怕就怕</p>', 0, 0, 1, 0, 1, 0, 1621328195, 1624439287, '给我查清楚');

INSERT INTO `ls_article_category`(`id`, `name`, `is_show`, `del`, `create_time`, `update_time`) VALUES (1, '阅文', 1, 0, 1619602123, 1619602123);

INSERT INTO `ls_article_category`(`id`, `name`, `is_show`, `del`, `create_time`, `update_time`) VALUES (2, '文章', 1, 0, 1623748425, 1623748425);

INSERT INTO `ls_config`(`id`, `shop_id`, `type`, `name`, `value`, `update_time`) VALUES (1, 0, 'marketing', 'invited_award_integral', '10', NULL);

INSERT INTO `ls_express` VALUES (1, '中通快递', 'uploads/images/20210615191706405b92371.png', 'https://www.zto.com/', 'ZTO', 'zhongtong', 1, 'ZTO', 1, 1605584048, 1690946575);
INSERT INTO `ls_express` VALUES (2, '顺丰快递', 'uploads/images/20220617113331dbd838350.png', 'www.shunfeng.cn', 'shunfeng', 'shunfeng', 0, 'SF', 1, 1611740650, 1690946574);
INSERT INTO `ls_express` VALUES (3, '圆通快递', 'uploads/images/202206171133312ca713276.png', 'www.yuantong.com', 'yuantong', 'yuantong', 11, 'yuantong', 1, 1612430142, 1690946572);
INSERT INTO `ls_express` VALUES (4, '邮政', 'uploads/images/20210518152230ec3538148.jpg', 'www.youzheng.cn', '514400', '514400', 2, '514400', 1, 1621322693, 1621322716);
INSERT INTO `ls_express` VALUES (5, 'RRD ', 'uploads/images/202106101710002f27c8195.png', 'FF', 'F ', 'VV ', 0, 'F ', 1, 1623327593, 1623327598);
INSERT INTO `ls_express` VALUES (6, '韵达', 'uploads/images/20210610145543666d87046.png', '', '', '', 0, '', 1, 1623327697, 1623328293);
INSERT INTO `ls_express` VALUES (7, '圆通', 'uploads/images/20210610145543666d87046.png', '', '', '', 0, '', 1, 1623328289, 1623328296);
INSERT INTO `ls_express` VALUES (8, '韵达', 'uploads/images/202106102042180d4ca0860.jpeg', '', '', '', 0, '', 1, 1623375757, 1690946570);
INSERT INTO `ls_express` VALUES (9, '百世汇通', 'uploads/images/20210615191706f6c599155.png', '', '', '', 12, '', 1, 1624436025, 1690946568);
INSERT INTO `ls_express` VALUES (10, '申通', 'uploads/images/20210615191706405b92371.png', '', '', '', 0, '', 1, 1624450183, 1624450192);

INSERT INTO `ls_express` VALUES (11, '圆通', 'images/express/YTO.png', 'www.yuantong.com', 'YTO', 'yuantong', 0, 'YTO', 0, 1690946702, NULL);
INSERT INTO `ls_express` VALUES (12, '中通', 'images/express/ZTO.png', 'https://www.zto.com/', 'ZTO', 'zhongtong', 0, 'ZTO', 0, 1690946729, NULL);
INSERT INTO `ls_express` VALUES (13, '申通', 'images/express/STO.png', 'http://www.sto.cn/', 'STO', 'shentong', 0, 'STO', 0, 1690946753, NULL);
INSERT INTO `ls_express` VALUES (14, '韵达', 'images/express/YD.png', 'http://www.yundaex.com/cn/index.php', 'YD', 'yunda', 0, 'YD', 0, 1690946796, NULL);
INSERT INTO `ls_express` VALUES (15, '德邦', 'images/express/DBL.png', 'https://www.deppon.com/', 'DBL', 'debangwuliu', 0, 'DBL', 0, 1690946821, NULL);
INSERT INTO `ls_express` VALUES (16, '百世', 'images/express/HTKY.png', 'https://www.800best.com/', 'HTKY', 'huitongkuaidi', 0, 'HTKY', 0, 1690946864, NULL);
INSERT INTO `ls_express` VALUES (17, '顺丰', 'images/express/SF.png', 'www.shunfeng.cn', 'SF', 'shunfeng', 0, 'SF', 0, 1690946899, NULL);
INSERT INTO `ls_express` VALUES (18, '极兔快递', 'images/express/JTSD.jpg', 'https://www.jtexpress.cn/', 'JTSD', 'jtexpress', 0, 'JTSD', 0, 1690946926, NULL);
INSERT INTO `ls_express` VALUES (19, '京东快递', 'images/express/JD.jpg', 'https://www.jdl.com/', 'JD', 'jd', 0, 'JD', 0, 1690946950, NULL);
INSERT INTO `ls_express` VALUES (20, '邮政标准快递', 'images/express/YZPY.png', 'https://www.ems.com.cn/', 'YZPY', 'youzhengguonei', 0, 'YZPY', 0, 1690947005, NULL);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (1, '20210611162313509254080.png', 1, 10, 'uploads/images/20210623151527cab885423.png', 1624432527, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (2, '20210611162314c22494251.png', 1, 10, 'uploads/images/202106231515271e4b99665.png', 1624432527, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (3, '202106111623069931f4392.png', 1, 10, 'uploads/images/20210623151527377608985.png', 1624432527, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (4, '20210611162313c74b06671.png', 1, 10, 'uploads/images/20210623151655a086e6634.png', 1624432615, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (5, '2021061116231337ba69535.png', 1, 10, 'uploads/images/20210623151655a1db60175.png', 1624432615, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (6, '20210611162306b725e3913.png', 1, 10, 'uploads/images/20210623151655f2f6f1247.png', 1624432615, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (7, 'u=540571034,3481547956&fm=11&fmt=auto&gp=0.png', 1, 10, 'uploads/images/20210623152309631260150.png', 1624432989, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (8, 'u=3477652920,4291506334&fm=26&fmt=auto&gp=0.png', 1, 10, 'uploads/images/20210623152309e67823417.png', 1624432989, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (9, 'u=3930806691,346019085&fm=11&fmt=auto&gp=0.png', 1, 10, 'uploads/images/202106231523094324f7660.png', 1624432989, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (10, 'u=2568371554,802314035&fm=26&fmt=auto&gp=0.png', 1, 10, 'uploads/images/2021062315230999d072189.png', 1624432989, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (11, 'u=739890615,2193933949&fm=26&fmt=auto&gp=0.png', 1, 10, 'uploads/images/20210623152309152573835.png', 1624432989, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (12, '1624434209(1).png', 7, 10, 'uploads/images/20210623154516b15806797.png', 1624434316, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (13, '1624434140(1).png', 7, 10, 'uploads/images/202106231545167297e8748.png', 1624434316, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (14, '1624434057(1).png', 7, 10, 'uploads/images/2021062315451692ae64452.png', 1624434316, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (15, 'a8d288855b3f61fd0810f70bde2d9b7e.jpeg', 1, 10, 'uploads/images/202106231548333d4b21360.jpeg', 1624434513, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (16, 'u=2479444746,3375077900&fm=26&fmt=auto&gp=0.png', 1, 10, 'uploads/images/20210623155107f09056250.png', 1624434667, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (17, 'u=3043278931,317319764&fm=26&fmt=auto&gp=0.png', 1, 10, 'uploads/images/202106231552064ef503917.png', 1624434726, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (18, 'G, google. logo (1).png', 1, 10, 'uploads/images/202106231553524cb454861.png', 1624434832, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (19, 'logo (1).png', 1, 10, 'uploads/images/202106231553528575a5046.png', 1624434832, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (20, 'milky-way-2695569_1280.jpg', 8, 10, 'uploads/images/2021062316011618df53902.jpg', 1624435276, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (21, 'seascape-4844697_1280.jpg', 8, 10, 'uploads/images/20210623160131eef1f5848.jpg', 1624435291, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (22, '1624434209(1).png', 9, 10, 'uploads/images/202106231606147b2621787.png', 1624435574, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (23, '1624434140(1).png', 9, 10, 'uploads/images/2021062316061477bd70629.png', 1624435574, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (24, '1624434057(1).png', 9, 10, 'uploads/images/20210623160614470f59417.png', 1624435574, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (25, '1624435718(1).png', 10, 10, 'uploads/images/2021062316100181cc98522.png', 1624435801, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (26, '1624435687(1).png', 10, 10, 'uploads/images/20210623161001cba752706.png', 1624435801, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (27, '1624435704(1).png', 10, 10, 'uploads/images/202106231610012e6d06145.png', 1624435801, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (28, '1624435735(1).png', 10, 10, 'uploads/images/20210623161001b06287677.png', 1624435801, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (29, '1624436198(1).png', 10, 10, 'uploads/images/20210623161928b7f498841.png', 1624436368, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (30, '1624436225(1).png', 10, 10, 'uploads/images/20210623161928a394c8252.png', 1624436368, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (31, '1624436644(1).png', 10, 10, 'uploads/images/2021062316242130cbe2184.png', 1624436661, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (32, '1624436627(1).png', 10, 10, 'uploads/images/20210623162421437884479.png', 1624436661, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (33, '1624436966(1).png', 10, 10, 'uploads/images/20210623163109a03085968.png', 1624437069, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (34, '1624436994(1).png', 10, 10, 'uploads/images/202106231631093150e8207.png', 1624437069, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (35, '1624436977(1).png', 10, 10, 'uploads/images/20210623163109330067724.png', 1624437069, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (36, '1624436954(1).png', 10, 10, 'uploads/images/20210623163109320811876.png', 1624437069, 0, 1, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (37, 'icon_bargain@2x.png', 1, 10, 'uploads/images/20210623164525339a81484.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (38, '组 9687@2x.png', 1, 10, 'uploads/images/20210623164525255c89351.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (39, 'icon_collect@2x(1).png', 1, 10, 'uploads/images/202106231645251f6264326.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (40, '组 9709@2x.png', 1, 10, 'uploads/images/20210623164525c1e7b2228.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (41, '组 9688@2x.png', 1, 10, 'uploads/images/2021062316452506ff00107.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (42, 'icon_news@2x.png', 1, 10, 'uploads/images/2021062316452596c500284.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (43, '组 12896@2x.png', 1, 10, 'uploads/images/202106231645256f5133787.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (44, '组 12897@2x.png', 1, 10, 'uploads/images/202106231645258357a4113.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (45, '组 12898@2x.png', 1, 10, 'uploads/images/20210623164525368bf6333.png', 1624437925, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (46, 'icon_collect@2x.png', 1, 10, 'uploads/images/20210623164702b03689886.png', 1624438022, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (47, '202106111636434dbd52891.png', 1, 10, 'uploads/images/2021062316502450f7e7606.png', 1624438224, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (48, 'icon_my_collection@2x.png', 1, 10, 'uploads/images/20210623165340b03f18918.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (49, 'icon_my_address@2x.png', 1, 10, 'uploads/images/202106231653409334c6907.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (50, 'icon_my_coupon@2x(1).png', 1, 10, 'uploads/images/20210623165340e0f787843.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (51, 'icon_my_coupon@2x.png', 1, 10, 'uploads/images/20210623165340f9dc75250.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (52, 'icon_my_help@2x.png', 1, 10, 'uploads/images/20210623165340c3afb3913.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (53, 'icon_my_fenxiao@2x.png', 1, 10, 'uploads/images/20210623165340a4a071710.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (54, 'icon_my_huiyuan@2x.png', 1, 10, 'uploads/images/202106231653409f6a76923.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (55, 'icon_my_kanjia@2x.png', 1, 10, 'uploads/images/20210623165340bf2312516.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (56, 'icon_my_news@2x.png', 1, 10, 'uploads/images/2021062316534090ba99956.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (57, 'icon_my_pintuan@2x(1).png', 1, 10, 'uploads/images/20210623165340428f06455.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (58, 'icon_my_wallet@2x.png', 1, 10, 'uploads/images/2021062316534022a9a0330.png', 1624438420, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (59, '20210611170111c412f0966.png', 1, 10, 'uploads/images/2021062316561070f0b5417.png', 1624438570, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (60, 'bee-6291207_1280.jpg', 8, 10, 'uploads/images/202106231658177efb99459.jpg', 1624438697, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (61, 'd4716e2bb5b883ba3db30c8dafb733c0.jpeg', 8, 10, 'uploads/images/20210623170018c9a413210.jpeg', 1624438818, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (62, '5f3ebd2a8d7e6c1ef2640f12a0201314.jpeg', 8, 10, 'uploads/images/2021062317001897f1e7584.jpeg', 1624438818, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (63, '9af1af3870924dff3fc5bacd1b4f49c0.jpeg', 8, 10, 'uploads/images/2021062317001844cf80052.jpeg', 1624438818, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (64, '8cf8b51eaffd11cb69cf25b6bc48a7af.jpeg', 8, 10, 'uploads/images/20210623170018d5e709551.jpeg', 1624438818, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (65, '3c119d6bbcd618984c26b700c572b9bb.jpeg', 8, 10, 'uploads/images/20210623170018ea28c8944.jpeg', 1624438818, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (66, 'e1f0f931ee4fdf1f7d269fae9c3f30a4.jpeg', 8, 10, 'uploads/images/202106231700190b5fa3047.jpeg', 1624438819, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (67, '20210615102802186e27399.png', 8, 10, 'uploads/images/20210623170129cad173742.png', 1624438889, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (68, 'G, google. logo (1).png', 1, 10, 'uploads/images/20210623171320939155531.png', 1624439600, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (69, '1624434057(1).png', 0, 10, 'uploads/images/202106231720103ae858398.png', 1624440010, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (70, 'icon_grade3@3x.png', 1, 10, 'uploads/images/202106231724520ebdd2219.png', 1624440292, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (71, 'icon_grade2@3x.png', 1, 10, 'uploads/images/20210623172452f8a0a0534.png', 1624440292, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (72, 'icon_grade4@3x.png', 1, 10, 'uploads/images/20210623172452ed11d9881.png', 1624440292, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (73, 'bg_silverMember@3x.png', 1, 10, 'uploads/images/2021062317245289a674915.png', 1624440292, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (74, '5443.png', 1, 10, 'uploads/images/202106231724527c65b6263.png', 1624440292, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (75, '34532.png', 1, 10, 'uploads/images/202106231724520057d9002.png', 1624440292, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (76, 'icon_grade@3x.png', 1, 10, 'uploads/images/202106231724528675d9412.png', 1624440292, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (77, '12424.png', 1, 10, 'uploads/images/202106231724527e12b7786.png', 1624440292, 0, 0, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (78, '1624440761(1).png', 0, 10, 'uploads/images/20210623173406abd797157.png', 1624440846, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (79, '1624440746(1).png', 0, 10, 'uploads/images/202106231734069e3f60769.png', 1624440846, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (80, '1624440800(1).png', 0, 10, 'uploads/images/20210623173406455837620.png', 1624440846, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (81, '1624441047(1).png', 0, 10, 'uploads/images/20210623173843e29a87344.png', 1624441123, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (82, '1624441063(1).png', 0, 10, 'uploads/images/20210623173843852687910.png', 1624441123, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (83, '1624441082(1).png', 0, 10, 'uploads/images/20210623173843f3c054393.png', 1624441123, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (84, '1624441476(1).png', 0, 10, 'uploads/images/20210623174458cd9c34209.png', 1624441498, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (85, 'Fe1LILmtyFgzc63e79dd9db6386948e22a498a854d7b.jpg', 0, 10, 'uploads/user/4/202106231813151b0022168.jpg', 1624443195, 0, 0, 4);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (86, '1624443338(1).png', 0, 10, 'uploads/images/202106231816381652f1876.png', 1624443398, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (87, '1624443320(1).png', 0, 10, 'uploads/images/20210623181638488155169.png', 1624443398, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (88, '1624443703(1).png', 0, 10, 'uploads/images/20210623182241cd7c65317.png', 1624443761, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (89, '1624443714(1).png', 0, 10, 'uploads/images/20210623182241a777f6424.png', 1624443761, 0, 2, 0);

INSERT INTO `ls_file`(`id`, `name`, `cid`, `type`, `uri`, `create_time`, `del`, `shop_id`, `user_id`) VALUES (90, '1624443690(1).png', 0, 10, 'uploads/images/202106231822414d13a7345.png', 1624443761, 0, 2, 0);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (1, 0, '图标', 0, 10, NULL, 10, 0, 1619421319, 1619421319);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (2, 1, '店铺分类1', 0, 10, 1, 3, 1, 1619915998, 1624435532);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (3, 1, '视频分类1', 0, 20, 1, 5, 0, 1620357578, 1620357578);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (4, 1, '蔬菜水果', 0, 10, 1, 1, 1, 1623220112, 1623220668);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (5, 1, '店铺分类2', 0, 10, 1, 2, 1, 1623222054, 1624435529);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (6, 1, '水果', 0, 10, 1, 20, 1, 1623222112, 1624435527);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (7, 0, '商品', 0, 10, 1, 1, 0, 1624434289, 1624434289);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (8, 0, '背景图', 0, 10, 1, 1, 0, 1624435265, 1624435265);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (9, 1, '图标', 0, 10, 1, 1, 0, 1624435546, 1624435546);

INSERT INTO `ls_file_cate`(`id`, `shop_id`, `name`, `pid`, `type`, `level`, `sort`, `del`, `create_time`, `update_time`) VALUES (10, 1, '商品图片', 0, 10, 1, 1, 0, 1624435556, 1624435556);

INSERT INTO `ls_goods`(`id`, `name`, `code`, `shop_id`, `shop_cate_id`, `first_cate_id`, `second_cate_id`, `third_cate_id`, `brand_id`, `unit_id`, `supplier_id`, `status`, `image`, `video`, `remark`, `content`, `sort`, `sales_actual`, `clicks`, `spec_type`, `max_price`, `min_price`, `market_price`, `stock`, `express_type`, `express_money`, `express_template_id`, `is_recommend`, `audit_status`, `audit_remark`, `create_time`, `update_time`, `del`, `stock_warn`, `column_ids`, `sales_virtual`, `sort_weight`, `poster`, `is_show_stock`, `is_member`, `is_distribution`, `first_ratio`, `second_ratio`, `third_ratio`) VALUES (1, 'BlackBerry/黑莓 KEYONE国行安卓全键盘指纹双卡移动联通电信4G', '34269268', 1, 1, 1, 2, 5, 3, 13, 6, 1, '/uploads/images/20210623161001b06287677.png', '', '黑莓信仰无敌', '<div id=\"attributes\" class=\"attributes\"><div id=\"tb_attributes\" class=\"tb-attributes\" data-spm-anchor-id=\"2013.1.0.i3.31bd13e8tVfozC\"><h3 data-spm=\"spu-attributes-more\" class=\"tb-attributes-title\" data-spm-anchor-id=\"2013.1.0.spu-attributes-more.31bd13e8tVfozC\">产品参数<a data-spm-click=\"gostr=/tbdetail;locaid=d1\" class=\"tb-attributes-more\">更多参数</a></h3><ul class=\"tb-attributes-list tb-attributes-fix\"><li><img src=\"https://gd1.alicdn.com/bao/uploaded/TB1hRW7IpXXXXXHXpXX760XFXXX\" alt=\"基本信息\"><p title=\"BlackBerry/黑莓\">品牌: BlackBerry/黑莓</p><p title=\"KEYONE\">黑莓型号: KEYONE</p></li><li><img src=\"https://gd1.alicdn.com/bao/uploaded/TB14LG.IpXXXXbfXpXX760XFXXX\" alt=\"屏幕\"><p title=\"1620x1080\">分辨率: 1620x1080</p></li><li><img src=\"https://gd1.alicdn.com/bao/uploaded/TB1kW11IpXXXXbVXFXX760XFXXX\" alt=\"网络\"><p title=\"4G\">网络类型: 4G</p><p title=\"双卡双待\">网络模式: 双卡双待</p></li><li><img src=\"https://gd1.alicdn.com/bao/uploaded/TB1LfniIpXXXXaYXXXX760XFXXX\" alt=\"CPU信息\"><p title=\"八核\">CPU核心数: 八核</p><p title=\"2.0G\">八核CPU频率: 2.0G</p></li><li><img src=\"https://gd1.alicdn.com/bao/uploaded/TB13LiTIpXXXXbPXVXX760XFXXX\" alt=\"存储\"><p title=\"4GB\">运行内存RAM: 4GB</p><p title=\"32GB 64GB\">存储容量: 32GB 64GB</p></li><li><img src=\"https://gd1.alicdn.com/bao/uploaded/TB10U53IpXXXXbrXFXX760XFXXX\" alt=\"拍照\"><p title=\"1200万\">后置摄像头: 1200万</p><p title=\"双摄像头（前后）\">摄像头类型: 双摄像头（前后）</p></li></ul><ul class=\"tb-attributes-sell tb-attributes-fix\"><li title=\"原封黑色 原封银色 原封棕榈金色 拆封黑色 拆封银色 拆封棕榈金色\">机身颜色: 原封黑色 原封银色 原封棕榈金色 拆封黑色 拆封银色 拆封棕榈金色</li><li title=\"官方标配\">套餐类型: 官方标配</li><li title=\"店铺三包\">售后服务: 店铺三包</li><li title=\"中国大陆\">版本类型: 中国大陆</li></ul></div></div><div id=\"tad_second_area\" class=\"tad-stage\" data-spm=\"4\"></div><div id=\"description\" class=\"J_DetailSection tshop-psm ke-post\"><div id=\"J_DivItemDesc\" class=\"content\"><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/609934833/O1CN014VrfwX1lZYle3HYkp_!!609934833.png\"></p><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/609934833/O1CN01rXQU3G1lZYlawCsYy_!!609934833.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/609934833/O1CN01VQsch11lZYlaaLcz4_!!609934833.png\"></p><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/609934833/O1CN01NeS8Xa1lZYlbYnky1_!!609934833.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i3/609934833/O1CN01QDD9n81lZYlStpi7m_!!609934833.png\" class=\"\" width=\"800\" height=\"327\"></p><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i3/609934833/O1CN01o9xvUK1lZYlcURrdv_!!609934833.png\" class=\"\" width=\"800\" height=\"956\"></p><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/609934833/O1CN0104SQEm1lZYldUJeWB_!!609934833.jpg\" class=\"\" width=\"800\" height=\"768\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/609934833/O1CN01AaqRGm1lZYlStvLmF_!!609934833.png\" class=\"\" width=\"861\" height=\"412\"></p><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/609934833/O1CN01QLmQ8y1lZYlb4nFK3_!!609934833.jpg\" class=\"\" width=\"800\" height=\"800\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/609934833/O1CN01BNuVRo1lZYlf7wQbi_!!609934833.png\" class=\"\" width=\"867\" height=\"269\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/609934833/O1CN01IGwVup1lZYlaaWyRS_!!609934833.jpg\" class=\"\" width=\"800\" height=\"800\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/609934833/O1CN01XIy78j1lZYldVgVoK_!!609934833.png\" class=\"\" width=\"800\" height=\"256\"></p><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/609934833/O1CN01gc4mPo1lZYlf8c3Gr_!!609934833.jpg\" class=\"\" width=\"800\" height=\"768\"></p><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/609934833/O1CN01euBEyk1lZYlcjWVAT_!!609934833.jpg\" class=\"\" width=\"800\" height=\"768\"></p><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/609934833/O1CN015pUzOp1lZYlXaZlLA_!!609934833.jpg\" class=\"\" width=\"800\" height=\"768\"></p><div><br></div></div></div>', 0, 0, 4, 2, 3699.00, 3699.00, 4399.00, 4080, 1, 0.00, 0, 1, 1, '', 1624435973, 1624439381, 0, 0, '1,2,3,5', 0, 0, '', 0, 1, 1, 10, 3, 5);

INSERT INTO `ls_goods`(`id`, `name`, `code`, `shop_id`, `shop_cate_id`, `first_cate_id`, `second_cate_id`, `third_cate_id`, `brand_id`, `unit_id`, `supplier_id`, `status`, `image`, `video`, `remark`, `content`, `sort`, `sales_actual`, `clicks`, `spec_type`, `max_price`, `min_price`, `market_price`, `stock`, `express_type`, `express_money`, `express_template_id`, `is_recommend`, `audit_status`, `audit_remark`, `create_time`, `update_time`, `del`, `stock_warn`, `column_ids`, `sales_virtual`, `sort_weight`, `poster`, `is_show_stock`, `is_member`, `is_distribution`, `first_ratio`, `second_ratio`, `third_ratio`) VALUES (2, '三星Galaxy S21 5G手机 6400万超高清120Hz超顺滑护目屏 骁龙888官方旗舰s21', '26966981', 1, 1, 1, 2, 4, 2, 13, 7, 1, '/uploads/images/20210623161928b7f498841.png', '', '强悍性能', '<div id=\"attributes\" class=\"attributes\"><div class=\"attributes-list\" id=\"J_AttrList\"><div class=\"tm-clear tb-hidden tm_brandAttr\" id=\"J_BrandAttr\" data-spm-anchor-id=\"a220o.1000855.0.i3.1bc35736PEy3QK\"><div class=\"name\">品牌名称：<span class=\"J_EbrandLogo\" target=\"_blank\" href=\"//brand.tmall.com/brandInfo.htm?brandId=81156&amp;type=0&amp;scm=1048.1.1.4\">Samsung/三星</span></div></div><p class=\"attr-list-hd tm-clear\"><a class=\"ui-more-nbg tm-MRswitchAttrs\" href=\"https://detail.tmall.com/item.htm?spm=a230r.1.14.35.7c06726ao5I4k5&amp;id=636230761911&amp;ns=1&amp;abbucket=19&amp;sku_properties=5919063:6536025;12304035:946237073;122216431:27772#J_Attrs\">更多参数<i class=\"ui-more-nbg-arrow tm-MRswitchAttrs\"></i></a><span>产品参数：</span></p><ul id=\"J_AttrUL\"><li title=\"Samsung/三星 Galaxy S21 5G SM-G9910\">产品名称：Samsung/三星 Galaxy S21...</li><li title=\"&nbsp;Galaxy S21 5G SM-G9910\">三星型号:&nbsp;Galaxy S21 5G SM-G9910</li><li title=\"&nbsp;丝雾白&nbsp;梵梦紫&nbsp;墨影灰\">机身颜色:&nbsp;丝雾白&nbsp;梵梦紫&nbsp;墨影灰</li><li title=\"&nbsp;8GB\">运行内存RAM:&nbsp;8GB</li><li title=\"&nbsp;8+256GB\">存储容量:&nbsp;8+256GB</li><li title=\"&nbsp;双卡双待单通\">网络模式:&nbsp;双卡双待单通</li><li title=\"&nbsp;骁龙888\">CPU型号:&nbsp;骁龙888</li><li title=\"&nbsp;高通骁龙888\">CPU型号:&nbsp;高通骁龙888</li><li title=\"&nbsp;以官网为准\">摄像头传感器型号:&nbsp;以官网为准</li></ul></div></div><div id=\"J_DcTopRightWrap\"><div id=\"J_DcTopRight\" class=\"J_DcAsyn tb-shop\"><div class=\"J_TModule\" data-widgetid=\"18122891587\" id=\"shop18122891587\" data-componentid=\"4004\" data-spm=\"110.0.4004-18122891587\" microscope-data=\"4004-18122891587\" data-title=\"宝贝推荐\"><div class=\"skin-box tb-module tshop-pbsm tshop-pbsm-shop-item-recommend\" tmallrecommend=\"1\" data-ald-rcmd=\"{&quot;url&quot;: &quot;//ald.taobao.com/recommend.htm?recommendItemIds=623711398425,648458498197,640985571254,644523678678,638371802678,643251983411,636233117356,645142495868&amp;needCount=8&amp;shopId=128573071&amp;sellerID=2616970884&amp;appID=03130&amp;isTmall=true&amp;moduleId=18122891587&quot;,\n                  &quot;showDiscount&quot;:  true ,\n                  &quot;showSellSituation&quot;:  false ,\n                  &quot;showEvaluateCount&quot;:  false ,\n                  &quot;showEvaluate&quot;:  false ,\n                  &quot;showItemGradeAvg&quot;: false,\n                  &quot;regionWidth&quot;: 790,\n                  &quot;picSize&quot;: 180 }\"><s class=\"skin-box-bt\"><b></b></s></div></div><div class=\"J_TModule\" data-widgetid=\"22297622750\" id=\"shop22297622750\" data-componentid=\"5003\" data-spm=\"110.0.5003-22297622750\" microscope-data=\"5003-22297622750\" data-title=\"自定义内容区\"></div></div></div><div id=\"description\" class=\"J_DetailSection tshop-psm tshop-psm-bdetaildes\"><div class=\"content ke-post\"><p><img src=\"https://img.alicdn.com/imgextra/i3/2616970884/O1CN01eUeTVz1IOumK5ekPA_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/2616970884/O1CN01PizHrF1IOumSUXFXx_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/2616970884/O1CN01ZGvQij1IOumK5eD9x_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i3/2616970884/O1CN01KpbHS11IOumNFZ7bB_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/2616970884/O1CN01WHYGi11IOumJSuby4_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i3/2616970884/O1CN01l8G5271IOumSuBBM3_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/2616970884/O1CN01nC9TtN1IOumLrqkrM_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i4/2616970884/O1CN01Q3BFOa1IOumLrtdfH_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i4/2616970884/O1CN01MA8xKg1IOumOMfNBv_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/2616970884/O1CN019fBo4s1IOumK5cjiP_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/2616970884/O1CN01NN4Ylr1IOumOMfyeU_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i2/2616970884/O1CN01N3aVyo1IOumDVuTy7_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i2/2616970884/O1CN01vcz2dq1IOumM3TqA1_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i2/2616970884/O1CN01SiwSNA1IOumN0RWQK_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i2/2616970884/O1CN01YKHPB91IOumS08qoX_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i3/2616970884/O1CN01O84ihg1IOumOMfJ4w_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i4/2616970884/O1CN01JEuMU51IOumM3TN6H_!!2616970884.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"></p></div></div>', 0, 0, 1, 1, 5367.00, 5367.00, 6896.00, 1023, 1, 0.00, 0, 1, 1, '', 1624436492, 1624444741, 0, 0, '1,2,3,5', 0, 0, '', 0, 1, 1, 10, 5, 3);

INSERT INTO `ls_goods`(`id`, `name`, `code`, `shop_id`, `shop_cate_id`, `first_cate_id`, `second_cate_id`, `third_cate_id`, `brand_id`, `unit_id`, `supplier_id`, `status`, `image`, `video`, `remark`, `content`, `sort`, `sales_actual`, `clicks`, `spec_type`, `max_price`, `min_price`, `market_price`, `stock`, `express_type`, `express_money`, `express_template_id`, `is_recommend`, `audit_status`, `audit_remark`, `create_time`, `update_time`, `del`, `stock_warn`, `column_ids`, `sales_virtual`, `sort_weight`, `poster`, `is_show_stock`, `is_member`, `is_distribution`, `first_ratio`, `second_ratio`, `third_ratio`) VALUES (3, 'iPhone 12 Pro', '72546662', 1, 1, 1, 2, 3, 1, 13, 7, 1, '/uploads/images/2021062316061477bd70629.png', '', '苹果', '<div id=\"attributes\" class=\"attributes\"><div class=\"attributes-list\" id=\"J_AttrList\"><div class=\"tm-clear tb-hidden tm_brandAttr\" id=\"J_BrandAttr\"><div class=\"name\" data-spm-anchor-id=\"a220o.1000855.0.i2.123147d4myIgfD\">品牌名称：<span class=\"J_EbrandLogo\" target=\"_blank\" href=\"//brand.tmall.com/brandInfo.htm?brandId=30111&amp;type=0&amp;scm=1048.1.1.4\">Apple/苹果</span></div></div></div></div><div id=\"J_TmpActBanner\"></div><div id=\"J_DcTopRightWrap\"></div><div id=\"description\" class=\"J_DetailSection tshop-psm tshop-psm-bdetaildes\"><div class=\"content ke-post\"><div><div><img src=\"https://img.alicdn.com/imgextra/i4/1917047079/O1CN01IvPN4u22AEKnCFHJl_!!1917047079.jpg\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\"></div><div><img src=\"https://img.alicdn.com/imgextra/i3/1917047079/O1CN01MzwjIq22AEP8ti91L_!!1917047079.jpg\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\" data-spm-anchor-id=\"a220o.1000855.0.i1.123147d4myIgfD\"></div><div><img src=\"https://img.alicdn.com/imgextra/i1/1917047079/O1CN01edflSa22AENVZZou0_!!1917047079.jpg\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\"></div><div><img src=\"https://img.alicdn.com/imgextra/i1/1917047079/O1CN01BSxoEp22AENTdjqFW_!!1917047079.png\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\"></div><div><img src=\"https://img.alicdn.com/imgextra/i4/1917047079/O1CN01DLhGVL22AEOYEuVHp_!!1917047079.jpg\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\"></div></div></div></div>', 0, 0, 1, 2, 7999.00, 7999.00, 9999.00, 7200, 1, 0.00, 0, 1, 1, '', 1624436849, 1624439369, 0, 0, '1,2,3,5', 0, 0, '', 0, 1, 1, 10, 5, 3);

INSERT INTO `ls_goods`(`id`, `name`, `code`, `shop_id`, `shop_cate_id`, `first_cate_id`, `second_cate_id`, `third_cate_id`, `brand_id`, `unit_id`, `supplier_id`, `status`, `image`, `video`, `remark`, `content`, `sort`, `sales_actual`, `clicks`, `spec_type`, `max_price`, `min_price`, `market_price`, `stock`, `express_type`, `express_money`, `express_template_id`, `is_recommend`, `audit_status`, `audit_remark`, `create_time`, `update_time`, `del`, `stock_warn`, `column_ids`, `sales_virtual`, `sort_weight`, `poster`, `is_show_stock`, `is_member`, `is_distribution`, `first_ratio`, `second_ratio`, `third_ratio`) VALUES (4, 'iphone12mini', '72033326', 1, 1, 1, 2, 3, 1, 13, 7, 1, '/uploads/images/20210623163109320811876.png', '', 'mini', '<div class=\"main-wrap  J_TRegion\" id=\"J_MainWrap\"><div class=\"sub-wrap\" id=\"J_SubWrap\"><div id=\"attributes\" class=\"attributes\"><div id=\"tb_attributes\" class=\"tb-attributes\"><h3 data-spm=\"spu-attributes-more\" class=\"tb-attributes-title\" data-spm-anchor-id=\"2013.1.0.spu-attributes-more.51ab1f25FZHbm2\">产品参数<a data-spm-click=\"gostr=/tbdetail;locaid=d1\" class=\"tb-attributes-more\">更多参数</a></h3><ul class=\"tb-attributes-list tb-attributes-fix\"><li><br></li><li><p title=\"1200万\">后置摄像头: 1200万</p><p title=\"三摄像头（后双）\">摄像头类型: 三摄像头（后双）</p></li></ul><ul class=\"tb-attributes-sell tb-attributes-fix\"><li title=\"iPhone 12  6.1英寸 黑色 iPhone 12  6.1英寸 白色 iPhone 12  6.1英寸 红色 iPhone 12  6.1英寸 蓝色 iPhone 12  6.1英寸 绿色 iPhone 12  6.1英寸 紫色 iPhone 12 mini 5.4英寸 黑色 iPhone 12 mini 5.4英寸 白色 iPhone 12 mini 5.4英寸 红色 iPhone 12 mini 5.4英寸 蓝色 iPhone 12 mini 5.4英寸 绿色 iPhone 12 mini 5.4英寸 紫色\">机身颜色: iPhone 12 6.1英寸 黑色 iPhone 12 6.1英寸 白色 iPhone 12 6.1英寸 红色 iPhone 12 6.1英寸 蓝色 iPhone 12 6.1英寸 绿色 iPhone 12 6.1英寸 紫色 iPhone 12 mini 5.4英寸 黑色 iPhone 12 mini 5.4英寸 白色 iPhone 12 mini 5.4英寸 红色 iPhone 12 mini 5.4英寸 蓝色 iPhone 12 mini 5.4英寸 绿色 iPhone 12 mini 5.4英寸 紫色</li><li title=\"官方标配 套餐一 套餐二\">套餐类型: 官方标配 套餐一 套餐二</li><li title=\"全国联保\">售后服务: 全国联保</li><li title=\"中国大陆\">版本类型: 中国大陆</li></ul></div></div><div id=\"tad_second_area\" class=\"tad-stage\" data-spm=\"4\"></div><div id=\"description\" class=\"J_DetailSection tshop-psm ke-post\"><div id=\"J_DivItemDesc\" class=\"content\"><p style=\"text-align: center;\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/12573474/O1CN01r0h0dJ1bX8bxsLik1_!!12573474.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/12573474/O1CN01R6DW0M1bX8aZr2gIM_!!12573474.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/12573474/O1CN01R1Bp7R1bX8aV96Qb4_!!12573474.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/12573474/O1CN019zp5yp1bX8aWFvHzs_!!12573474.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/12573474/O1CN01OmQmJe1bX8aYMlPt6_!!12573474.jpg\" class=\"\" width=\"750\" height=\"578\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/12573474/O1CN019IWI8R1bX8Z5tdx4L_!!12573474.jpg\" class=\"\" width=\"750\" height=\"492\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/12573474/O1CN01qtc2LI1bX8Z3sK9Rb_!!12573474.jpg\" class=\"\" width=\"750\" height=\"787\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/12573474/O1CN01aUdsiK1bX8bpPjkiZ_!!12573474.jpg\" class=\"\" width=\"750\" height=\"422\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/12573474/O1CN01EQLZrQ1bX8Z9lsojS_!!12573474.jpg\" class=\"\" width=\"750\" height=\"501\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/12573474/O1CN01BkJJvw1bX8Z8Ytp2S_!!12573474.jpg\" class=\"\" width=\"750\" height=\"451\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i3/12573474/O1CN01gYNEub1bX8Z9ltxRS_!!12573474.jpg\" class=\"\" width=\"750\" height=\"514\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/12573474/O1CN013pXTHI1bX8Z8s1ozP_!!12573474.jpg\" class=\"\" width=\"750\" height=\"537\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/12573474/O1CN01IRO3kE1bX8ZBdSU8t_!!12573474.jpg\" class=\"\" width=\"750\" height=\"498\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/12573474/O1CN015fN2cB1bX8Z5zg6Hs_!!12573474.jpg\" class=\"\" width=\"750\" height=\"565\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/12573474/O1CN01et3mfT1bX8Z7qNWDS_!!12573474.jpg\" class=\"\" width=\"750\" height=\"535\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i3/12573474/O1CN01xlPmLr1bX8Z8YvV0U_!!12573474.jpg\" class=\"\" width=\"750\" height=\"464\"></p></div></div></div><div class=\"J_AsyncDC tb-custom-area tb-shop\" data-type=\"main\" id=\"J_AsyncDCMain\"><div class=\"J_TModule\" data-widgetid=\"14469173604\" id=\"shop14469173604\" data-componentid=\"4018\" data-spm=\"110.0.4018-14469173604\" microscope-data=\"4018-14469173604\" data-title=\"旺铺关联推荐\"><div class=\"skin-box tb-module tshop-pbsm tshop-pbsm-other-guanliantuijian\"></div></div></div></div><div class=\"tb-price-spec\"><h3 class=\"spec-title\">价格说明</h3><p class=\"title\">划线价格</p><p class=\"info\">指商品的专柜价、吊牌价、正品零售价、厂商指导价或该商品的曾经展示过的销售价等，<span>并非原价</span>，仅供参考。</p><p class=\"title\">未划线价格</p><p class=\"info\">指商品的<span>实时标价</span>，不因表述的差异改变性质。具体成交价格根据商品参加活动，或会员使用优惠券、积分等发生变化，最终以订单结算页价格为准。</p><p class=\"info\">商家详情页（含主图）以图片或文字形式标注的一口价、促销价、优惠价等价格可能是在使用优惠券、满减或特定优惠活动和时段等情形下的价格，具体请以结算页面的标价、优惠条件或活动规则为准。</p><p class=\"info\">此说明仅当出现价格比较时有效，具体请参见《淘宝价格发布规范》。若商家单独对划线价格进行说明的，以商家的表述为准。</p></div>', 0, 1, 3, 2, 4369.00, 4369.00, 7760.00, 7197, 1, 0.00, 0, 1, 1, '', 1624437262, 1624442415, 0, 0, '1,2,3,5', 0, 0, '', 0, 1, 1, 10, 5, 3);

INSERT INTO `ls_goods`(`id`, `name`, `code`, `shop_id`, `shop_cate_id`, `first_cate_id`, `second_cate_id`, `third_cate_id`, `brand_id`, `unit_id`, `supplier_id`, `status`, `image`, `video`, `remark`, `content`, `sort`, `sales_actual`, `clicks`, `spec_type`, `max_price`, `min_price`, `market_price`, `stock`, `express_type`, `express_money`, `express_template_id`, `is_recommend`, `audit_status`, `audit_remark`, `create_time`, `update_time`, `del`, `stock_warn`, `column_ids`, `sales_virtual`, `sort_weight`, `poster`, `is_show_stock`, `is_member`, `is_distribution`, `first_ratio`, `second_ratio`, `third_ratio`) VALUES (5, 'iPhone 11 Pro', '97414906', 2, 2, 1, 2, 3, 1, 13, 7, 1, '/uploads/images/20210623174458cd9c34209.png', '', '好看', '<div id=\"attributes\" class=\"attributes\"><div id=\"tb_attributes\" class=\"tb-attributes\"><ul class=\"tb-attributes-sell tb-attributes-fix\" data-spm-anchor-id=\"2013.1.0.i2.75b96878p3DL22\"><li title=\"iPhone11 6.1英寸【黑色】 iPhone11 6.1英寸【白色】 iPhone11 6.1英寸【绿色】 iPhone11 6.1英寸【紫色】 iPhone11 6.1英寸【黄色】 iPhone11 6.1英寸【红色】 苹果11 6.1寸红色USA单机\">机身颜色: iPhone11 6.1英寸【黑色】 iPhone11 6.1英寸【白色】 iPhone11 6.1英寸【绿色】 iPhone11 6.1英寸【紫色】 iPhone11 6.1英寸【黄色】 iPhone11 6.1英寸【红色】 苹果11 6.1寸红色USA单机</li><li title=\"官方标配 套餐一\">套餐类型: 官方标配 套餐一</li><li title=\"店铺三包\">售后服务: 店铺三包</li><li title=\"中国大陆\">版本类型: 中国大陆</li></ul></div></div><div id=\"tad_second_area\" class=\"tad-stage\" data-spm=\"4\"></div><div id=\"description\" class=\"J_DetailSection tshop-psm ke-post\"><div id=\"J_DivItemDesc\" class=\"content\"><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/708804205/O1CN01zgg0Vr1gvw66Zgpn0_!!708804205.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i3/708804205/O1CN012X5OoR1gvw69ZNKAI_!!708804205.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i3/708804205/O1CN01tcpkSr1gvw63uVHVK_!!708804205.png\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/708804205/O1CN01HC0Nli1gvw69BFeDG_!!708804205.png\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/708804205/O1CN01tQXEYJ1gvw6B7WTRS_!!708804205.png\" class=\"\" width=\"750\" height=\"1412\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/708804205/O1CN01Ylj0SS1gvw6CNOvIs_!!708804205.png\" class=\"\" width=\"790\" height=\"700\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/708804205/O1CN01Gmf2yk1gvw67BXnqG_!!708804205.png\" class=\"\" width=\"790\" height=\"709\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/708804205/O1CN019pUvif1gvw69BHaqJ_!!708804205.png\" class=\"\" width=\"790\" height=\"624\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i3/708804205/O1CN01y1afY61gvw62jS7mf_!!708804205.png\" class=\"\" width=\"790\" height=\"726\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/708804205/O1CN01uzuboA1gvw6AT64pU_!!708804205.png\" class=\"\" width=\"790\" height=\"695\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/708804205/O1CN01Mb7OsY1gvw6CNSHHE_!!708804205.png\" class=\"\" width=\"790\" height=\"699\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/708804205/O1CN01DuZWdb1gvw68THBHZ_!!708804205.png\" class=\"\" width=\"790\" height=\"694\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/708804205/O1CN01asTQT61gvw68TGNPP_!!708804205.png\" class=\"\" width=\"790\" height=\"697\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/708804205/O1CN01gJB4hY1gvw68TFdfI_!!708804205.png\" class=\"\" width=\"790\" height=\"695\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/708804205/O1CN011ddcye1gvw69BKD73_!!708804205.png\" class=\"\" width=\"790\" height=\"695\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/708804205/O1CN012siSam1gvw63uXYzu_!!708804205.png\" class=\"\" width=\"790\" height=\"675\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/708804205/O1CN01LMECG11gvw67NKE9Y_!!708804205.png\" class=\"\" width=\"790\" height=\"1295\"></p></div></div>', 0, 0, 0, 2, 4633.00, 4633.00, 6899.00, 4800, 1, 0.00, 0, 1, 1, '', 1624440989, 1624443570, 0, 0, '1,2,3,5', 0, 0, '', 0, 1, 1, 10, 5, 3);

INSERT INTO `ls_goods`(`id`, `name`, `code`, `shop_id`, `shop_cate_id`, `first_cate_id`, `second_cate_id`, `third_cate_id`, `brand_id`, `unit_id`, `supplier_id`, `status`, `image`, `video`, `remark`, `content`, `sort`, `sales_actual`, `clicks`, `spec_type`, `max_price`, `min_price`, `market_price`, `stock`, `express_type`, `express_money`, `express_template_id`, `is_recommend`, `audit_status`, `audit_remark`, `create_time`, `update_time`, `del`, `stock_warn`, `column_ids`, `sales_virtual`, `sort_weight`, `poster`, `is_show_stock`, `is_member`, `is_distribution`, `first_ratio`, `second_ratio`, `third_ratio`) VALUES (6, 'iphone xs max', '50811948', 2, 2, 1, 2, 3, 1, 13, 7, 1, '/uploads/images/20210623173843e29a87344.png', '', '好看好用', '<div id=\"attributes\" class=\"attributes\"><div id=\"tb_attributes\" class=\"tb-attributes\"><ul class=\"tb-attributes-sell tb-attributes-fix\" data-spm-anchor-id=\"2013.1.0.i1.151034d1I94RGa\"><li title=\"苹果X 5.8寸【深灰色】 苹果X 5.8寸【银白色】 iPhone XR 6.1英寸【白色】 iPhone XR 6.1英寸【黑色】 iPhone XR 6.1英寸【红色】 iPhone XR 6.1英寸【黄色】 iPhone XR 6.1英寸【珊瑚色】 iPhone XR 6.1英寸【蓝色】 苹果XS MAX 6.5寸【银白色】 苹果XS MAX 6.5寸【深灰色】 苹果XS MAX 6.5寸【金色】 苹果XS 5.8寸 【银白色】 苹果XS 5.8寸 【深灰色】 苹果XS 5.8寸 【金色】\">机身颜色: 苹果X 5.8寸【深灰色】 苹果X 5.8寸【银白色】 iPhone XR 6.1英寸【白色】 iPhone XR 6.1英寸【黑色】 iPhone XR 6.1英寸【红色】 iPhone XR 6.1英寸【黄色】 iPhone XR 6.1英寸【珊瑚色】 iPhone XR 6.1英寸【蓝色】 苹果XS MAX 6.5寸【银白色】 苹果XS MAX 6.5寸【深灰色】 苹果XS MAX 6.5寸【金色】 苹果XS 5.8寸 【银白色】 苹果XS 5.8寸 【深灰色】 苹果XS 5.8寸 【金色】</li><li title=\"官方标配 套餐一\">套餐类型: 官方标配 套餐一</li><li title=\"店铺三包\">售后服务: 店铺三包</li><li title=\"中国大陆\">版本类型: 中国大陆</li></ul></div></div><div id=\"tad_second_area\" class=\"tad-stage\" data-spm=\"4\"></div><div id=\"description\" class=\"J_DetailSection tshop-psm ke-post\"><div id=\"J_DivItemDesc\" class=\"content\"><p><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/1623397783/O1CN016VYuaB27MfC4IFA6w_!!1623397783.jpg\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/1623397783/O1CN01U3VTHs27MfC4IF1nN_!!1623397783.jpg\"><img align=\"absmiddle\" height=\"1465\" src=\"https://img.alicdn.com/imgextra/i2/1623397783/O1CN01yYSWOd27MfC2Bx4Kv_!!1623397783.jpg\" class=\"\" width=\"790\"><img align=\"absmiddle\" height=\"1024\" src=\"https://img.alicdn.com/imgextra/i2/1623397783/O1CN01UqvH3S27MfCR9uEOF_!!1623397783.png\" class=\"\" width=\"790\">&nbsp;<img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/1623397783/O1CN01hmAVN527MfCeXu5Bs_!!1623397783.jpg\" class=\"\" width=\"790\" height=\"1443\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i1/1623397783/O1CN01QKBXPK27MfCZzlS6F_!!1623397783.jpg\" class=\"\" width=\"790\" height=\"1444\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/1623397783/O1CN0102OvCe27MfCS615g5_!!1623397783.jpg\" class=\"\" width=\"790\" height=\"1444\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i3/1623397783/O1CN01xpVK7l27MfCb0sWWs_!!1623397783.jpg\" class=\"\" width=\"790\" height=\"1445\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i4/1623397783/O1CN01WnNPvv27MfCeXs8fT_!!1623397783.jpg\" class=\"\" width=\"790\" height=\"1444\"><img align=\"absmiddle\" src=\"https://img.alicdn.com/imgextra/i2/1623397783/O1CN01JyVNPz27MfCZzmnD1_!!1623397783.jpg\" class=\"\" width=\"790\" height=\"1076\"></p></div></div>', 0, 0, 1, 2, 3160.00, 3160.00, 5000.00, 7200, 1, 0.00, 0, 1, 1, '', 1624441433, 1624443562, 0, 0, '1,2,3,5', 0, 0, '', 0, 1, 1, 10, 5, 3);

INSERT INTO `ls_goods`(`id`, `name`, `code`, `shop_id`, `shop_cate_id`, `first_cate_id`, `second_cate_id`, `third_cate_id`, `brand_id`, `unit_id`, `supplier_id`, `status`, `image`, `video`, `remark`, `content`, `sort`, `sales_actual`, `clicks`, `spec_type`, `max_price`, `min_price`, `market_price`, `stock`, `express_type`, `express_money`, `express_template_id`, `is_recommend`, `audit_status`, `audit_remark`, `create_time`, `update_time`, `del`, `stock_warn`, `column_ids`, `sales_virtual`, `sort_weight`, `poster`, `is_show_stock`, `is_member`, `is_distribution`, `first_ratio`, `second_ratio`, `third_ratio`) VALUES (7, 'Samsung SM-N9860 骁龙865+三星note20官方旗舰店5g手机', '19365068', 2, 2, 1, 2, 4, 2, 13, 6, 1, '/uploads/images/20210623181638488155169.png', '', '好看', '<div id=\"attributes\" class=\"attributes\"><div class=\"attributes-list\" id=\"J_AttrList\"><div class=\"tm-clear tb-hidden tm_brandAttr\" id=\"J_BrandAttr\" data-spm-anchor-id=\"a220o.1000855.0.i0.5fc113f383y6zD\"><div class=\"name\">品牌名称：<span class=\"J_EbrandLogo\" target=\"_blank\" href=\"//brand.tmall.com/brandInfo.htm?brandId=81156&amp;type=0&amp;scm=1048.1.1.4\">Samsung/三星</span></div></div><p class=\"attr-list-hd tm-clear\"><a class=\"ui-more-nbg tm-MRswitchAttrs\" href=\"https://detail.tmall.com/item.htm?spm=a1z10.1-b-s.w17839663-14649061305.15.6dd94957rsNpAA&amp;id=624998815046&amp;scene=taobao_shop&amp;sku_properties=5919063:6536025;122216431:27772#J_Attrs\">更多参数<i class=\"ui-more-nbg-arrow tm-MRswitchAttrs\"></i></a><span>产品参数：</span></p><ul id=\"J_AttrUL\"><li title=\"2020011606297143\">证书编号：2020011606297143</li><li title=\"有效\">证书状态：有效</li><li title=\"5G数字移动电话机\">产品名称：5G数字移动电话机</li><li title=\"SM-N9860（旅行充电器：EP-TA800 输出 :(PD0) 5.0Vdc 3.0A 或9.0...\">3C规格型号：SM-N9860（旅行充电器：EP-TA800 输出 :(PD0) 5.0Vdc 3.0A 或9.0...</li><li title=\"Samsung/三星 Galaxy Note20 Ultra 5G SM-N9860\">产品名称：Samsung/三星 Galaxy Not...</li><li title=\"&nbsp;Galaxy Note20 Ultra 5G SM-N9860\">三星型号:&nbsp;Galaxy Note20 Ultra 5G SM-N9860</li><li title=\"&nbsp;曜岩黑&nbsp;迷雾金&nbsp;初露白\">机身颜色:&nbsp;曜岩黑&nbsp;迷雾金&nbsp;初露白</li><li title=\"&nbsp;12GB\">运行内存RAM:&nbsp;12GB</li><li title=\"&nbsp;12+512GB&nbsp;12+256GB\">存储容量:&nbsp;12+512GB&nbsp;12+256GB</li><li title=\"&nbsp;双卡双待单通\">网络模式:&nbsp;双卡双待单通</li><li title=\"&nbsp;高通骁龙865+处理器\">CPU型号:&nbsp;高通骁龙865+处理器</li></ul></div></div><div id=\"mall-banner\"><div data-spm=\"1998132255\"></div><div id=\"J_DescTMS1\"></div></div><div id=\"J_TmpActBanner\"></div><div id=\"J_DcTopRightWrap\"><div id=\"J_DcTopRight\" class=\"J_DcAsyn tb-shop\"><div class=\"J_TModule\" data-widgetid=\"23580933490\" id=\"shop23580933490\" data-componentid=\"5003\" data-spm=\"110.0.5003-23580933490\" microscope-data=\"5003-23580933490\" data-title=\"自定义内容区\"><div class=\"skin-box tb-module tshop-pbsm tshop-pbsm-shop-self-defined\"><s class=\"skin-box-tp\"><b></b></s><div class=\"skin-box-bd clear-fix\"><p><br></p></div><s class=\"skin-box-bt\"><b></b></s></div></div></div></div><div id=\"description\" class=\"J_DetailSection tshop-psm tshop-psm-bdetaildes\"><div class=\"content ke-post\"><p><a href=\"https://pages.tmall.com/wow/a/act/tmall/dailygroup/606/wupr?wh_pid=daily-228829&amp;itemId=624998815046\" target=\"_blank\"><img src=\"https://img.alicdn.com/imgextra/i2/370627083/O1CN017MXtD622C3zphunWG_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"></a><img src=\"https://img.alicdn.com/imgextra/i3/370627083/O1CN01B7Fjlt22C3wyu4jLK_!!370627083.png\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i2/370627083/O1CN01Sjzvmx22C3x0lQhcV_!!370627083.png\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i3/370627083/O1CN01Hc12p422C3wxjLRij_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i4/370627083/O1CN01Eqbl8F22C3wzNakba_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i4/370627083/O1CN01juof5u22C3x1yHVYS_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i3/370627083/O1CN01ehASEo22C3wxb3ayJ_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/370627083/O1CN016rkLYW22C3wzNcIPg_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/370627083/O1CN01gofc5e22C3wxjOs0R_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i4/370627083/O1CN0157QhJ022C3x01enSX_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i1/370627083/O1CN01MMY9Kx22C3wutAexb_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"><img src=\"https://img.alicdn.com/imgextra/i3/370627083/O1CN01VBrRFq22C3xFvmKDs_!!370627083.jpg\" align=\"absmiddle\" class=\"img-ks-lazyload\"></p></div></div>', 0, 0, 0, 1, 6666.00, 6666.00, 9999.00, 1020, 1, 0.00, 0, 1, 1, '', 1624443457, 1624443556, 0, 0, '1,2,3,5', 0, 0, '', 0, 1, 1, 10, 5, 3);

INSERT INTO `ls_goods`(`id`, `name`, `code`, `shop_id`, `shop_cate_id`, `first_cate_id`, `second_cate_id`, `third_cate_id`, `brand_id`, `unit_id`, `supplier_id`, `status`, `image`, `video`, `remark`, `content`, `sort`, `sales_actual`, `clicks`, `spec_type`, `max_price`, `min_price`, `market_price`, `stock`, `express_type`, `express_money`, `express_template_id`, `is_recommend`, `audit_status`, `audit_remark`, `create_time`, `update_time`, `del`, `stock_warn`, `column_ids`, `sales_virtual`, `sort_weight`, `poster`, `is_show_stock`, `is_member`, `is_distribution`, `first_ratio`, `second_ratio`, `third_ratio`) VALUES (8, '苹果 20W USB-C 电源适配器', '91178040', 2, 2, 1, 2, 3, 1, 15, 6, 1, '/uploads/images/20210623182241cd7c65317.png', '', '好看', '<div id=\"attributes\" class=\"attributes\"><div class=\"attributes-list\" id=\"J_AttrList\"><div class=\"tm-clear tb-hidden tm_brandAttr\" id=\"J_BrandAttr\"><div class=\"name\" data-spm-anchor-id=\"a220o.1000855.0.i0.19a95c4caVRrg2\">品牌名称：<span class=\"J_EbrandLogo\" target=\"_blank\" href=\"//brand.tmall.com/brandInfo.htm?brandId=30111&amp;type=0&amp;scm=1048.1.1.4\">Apple/苹果</span></div></div></div></div><div id=\"J_TmpActBanner\"></div><div id=\"J_DcTopRightWrap\"></div><div id=\"description\" class=\"J_DetailSection tshop-psm tshop-psm-bdetaildes\"><div class=\"content ke-post\"><div><div><img src=\"https://img.alicdn.com/imgextra/i4/1917047079/O1CN01IvPN4u22AEKnCFHJl_!!1917047079.jpg\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\"></div><div><img src=\"https://img.alicdn.com/imgextra/i3/1917047079/O1CN01JLBWdo22AEOhdg8wr_!!1917047079.png\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\"></div><div><img src=\"https://img.alicdn.com/imgextra/i1/1917047079/O1CN01XfJU6k22AEQXsYyhs_!!1917047079.png\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\"></div><div><img src=\"https://img.alicdn.com/imgextra/i4/1917047079/O1CN01DLhGVL22AEOYEuVHp_!!1917047079.jpg\" width=\"790\" border=\"0\" class=\"img-ks-lazyload\"></div></div></div></div>', 3, 0, 1, 1, 149.00, 149.00, 149.00, 1023, 1, 0.00, 0, 1, 1, '', 1624443820, 1624443856, 0, 0, '1,2,3,5', 0, 3, '', 0, 1, 1, 10, 5, 3);

INSERT INTO `ls_goods_brand`(`id`, `name`, `image`, `initial`, `is_show`, `sort`, `remark`, `create_time`, `update_time`, `del`) VALUES (1, '苹果', 'uploads/images/202106231548333d4b21360.jpeg', 'A', 1, 0, '', 1621049304, 1624435054, 0);

INSERT INTO `ls_goods_brand`(`id`, `name`, `image`, `initial`, `is_show`, `sort`, `remark`, `create_time`, `update_time`, `del`) VALUES (2, '三星', 'uploads/images/20210623155107f09056250.png', 'S', 1, 0, '', 1623310198, 1624435088, 0);

INSERT INTO `ls_goods_brand`(`id`, `name`, `image`, `initial`, `is_show`, `sort`, `remark`, `create_time`, `update_time`, `del`) VALUES (3, '黑莓', 'uploads/images/202106231552064ef503917.png', 'H', 1, 0, '', 1623329141, 1624435078, 0);

INSERT INTO `ls_goods_category`(`id`, `name`, `pid`, `level`, `sort`, `is_show`, `image`, `bg_image`, `remark`, `create_time`, `update_time`, `del`) VALUES (1, '数码', 0, 1, 0, 1, '', 'uploads/images/2021062315451692ae64452.png', '', 1624434356, 1624434356, 0);

INSERT INTO `ls_goods_category`(`id`, `name`, `pid`, `level`, `sort`, `is_show`, `image`, `bg_image`, `remark`, `create_time`, `update_time`, `del`) VALUES (2, '手机', 1, 2, 0, 1, 'uploads/images/202106231545167297e8748.png', '', '', 1624434370, 1624434370, 0);

INSERT INTO `ls_goods_category`(`id`, `name`, `pid`, `level`, `sort`, `is_show`, `image`, `bg_image`, `remark`, `create_time`, `update_time`, `del`) VALUES (3, '苹果', 2, 3, 0, 1, 'uploads/images/202106231548333d4b21360.jpeg', '', '', 1624434517, 1624434517, 0);

INSERT INTO `ls_goods_category`(`id`, `name`, `pid`, `level`, `sort`, `is_show`, `image`, `bg_image`, `remark`, `create_time`, `update_time`, `del`) VALUES (4, '三星', 2, 3, 0, 1, 'uploads/images/20210623155107f09056250.png', '', '', 1624434671, 1624434671, 0);

INSERT INTO `ls_goods_category`(`id`, `name`, `pid`, `level`, `sort`, `is_show`, `image`, `bg_image`, `remark`, `create_time`, `update_time`, `del`) VALUES (5, '黑莓', 2, 3, 0, 1, 'uploads/images/202106231552064ef503917.png', '', '', 1624434729, 1624434729, 0);

INSERT INTO `ls_goods_column`(`id`, `name`, `remark`, `sort`, `status`, `create_time`, `update_time`, `del`) VALUES (1, '好物优选', '精选推荐', 0, 1, 1618471498, 1620799537, 0);

INSERT INTO `ls_goods_column`(`id`, `name`, `remark`, `sort`, `status`, `create_time`, `update_time`, `del`) VALUES (2, '新品好物', '24小时热销', 0, 1, 1620799553, 1620799553, 0);

INSERT INTO `ls_goods_column`(`id`, `name`, `remark`, `sort`, `status`, `create_time`, `update_time`, `del`) VALUES (3, '海外进口', '国际进口商品', 0, 1, 1620799585, 1620799585, 0);

INSERT INTO `ls_goods_column`(`id`, `name`, `remark`, `sort`, `status`, `create_time`, `update_time`, `del`) VALUES (4, '说你看了看', '发发发发发发奥二', 0, 1, 1623207590, 1623207723, 1);

INSERT INTO `ls_goods_column`(`id`, `name`, `remark`, `sort`, `status`, `create_time`, `update_time`, `del`) VALUES (5, '热销磅单', '热销榜单', 0, 1, 1623831247, 1624435112, 0);

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (1, 1, '/uploads/images/2021062316100181cc98522.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (2, 1, '/uploads/images/20210623161001cba752706.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (3, 1, '/uploads/images/202106231610012e6d06145.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (4, 1, '/uploads/images/20210623161001b06287677.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (5, 2, '/uploads/images/20210623161928b7f498841.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (6, 2, '/uploads/images/20210623161928a394c8252.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (10, 3, '/uploads/images/2021062316061477bd70629.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (11, 3, '/uploads/images/2021062316242130cbe2184.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (12, 3, '/uploads/images/20210623162421437884479.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (13, 4, '/uploads/images/20210623163109a03085968.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (14, 4, '/uploads/images/202106231631093150e8207.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (15, 4, '/uploads/images/20210623163109330067724.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (16, 4, '/uploads/images/20210623163109320811876.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (20, 6, '/uploads/images/20210623173843e29a87344.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (21, 6, '/uploads/images/20210623173843852687910.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (22, 6, '/uploads/images/20210623173843f3c054393.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (23, 5, '/uploads/images/20210623173406abd797157.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (24, 5, '/uploads/images/202106231734069e3f60769.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (25, 5, '/uploads/images/20210623173406455837620.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (26, 7, '/uploads/images/202106231816381652f1876.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (27, 7, '/uploads/images/20210623181638488155169.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (31, 8, '/uploads/images/20210623182241a777f6424.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (32, 8, '/uploads/images/20210623182241cd7c65317.png');

INSERT INTO `ls_goods_image`(`id`, `goods_id`, `uri`) VALUES (33, 8, '/uploads/images/202106231822414d13a7345.png');

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (1, '/uploads/images/20210623161001b06287677.png', 1, '1,3', '黑色,32GB', 4399.00, 3699.00, 1020, 1.000, 1.000, '100231536', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (2, '/uploads/images/20210623161001b06287677.png', 1, '1,4', '黑色,64GB', 4399.00, 3699.00, 1020, 1.000, 1.000, '100231536', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (3, '/uploads/images/20210623161001b06287677.png', 1, '2,3', '银色,32GB', 4399.00, 3699.00, 1020, 1.000, 1.000, '100231536', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (4, '/uploads/images/20210623161001b06287677.png', 1, '2,4', '银色,64GB', 4399.00, 3699.00, 1020, 1.000, 1.000, '100231536', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (5, '/uploads/images/20210623161928b7f498841.png', 2, '5', '默认', 6896.00, 5367.00, 1023, 1.000, 1.000, '1001235852', NULL);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (6, 'uploads/images/2021062316061477bd70629.png', 3, '6,9', '黑色,128GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (7, 'uploads/images/2021062316061477bd70629.png', 3, '6,10', '黑色,256GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (8, 'uploads/images/2021062316061477bd70629.png', 3, '6,11', '黑色,512GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (9, 'uploads/images/2021062316061477bd70629.png', 3, '7,9', '银色,128GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (10, 'uploads/images/2021062316061477bd70629.png', 3, '7,10', '银色,256GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (11, 'uploads/images/2021062316061477bd70629.png', 3, '7,11', '银色,512GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (12, 'uploads/images/2021062316061477bd70629.png', 3, '8,9', '海蓝色,128GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (13, 'uploads/images/2021062316061477bd70629.png', 3, '8,10', '海蓝色,256GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (14, 'uploads/images/2021062316061477bd70629.png', 3, '8,11', '海蓝色,512GB', 9999.00, 7999.00, 800, 1.000, 1.000, '1001235456', 4000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (15, '/uploads/images/20210623163109a03085968.png', 4, '12,15', '黑色,64GB', 7760.00, 4369.00, 797, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (16, '/uploads/images/20210623163109a03085968.png', 4, '12,16', '黑色,128GB', 7760.00, 4369.00, 800, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (17, '/uploads/images/20210623163109a03085968.png', 4, '12,17', '黑色,256GB', 7760.00, 4369.00, 800, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (18, '/uploads/images/20210623163109a03085968.png', 4, '13,15', '紫色,64GB', 7760.00, 4369.00, 800, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (19, '/uploads/images/20210623163109a03085968.png', 4, '13,16', '紫色,128GB', 7760.00, 4369.00, 800, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (20, '/uploads/images/20210623163109a03085968.png', 4, '13,17', '紫色,256GB', 7760.00, 4369.00, 800, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (21, '/uploads/images/20210623163109a03085968.png', 4, '14,15', '绿色,64GB', 7760.00, 4369.00, 800, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (22, '/uploads/images/20210623163109a03085968.png', 4, '14,16', '绿色,128GB', 7760.00, 4369.00, 800, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (23, '/uploads/images/20210623163109a03085968.png', 4, '14,17', '绿色,256GB', 7760.00, 4369.00, 800, 1.000, 1.000, '1002123536', 3500.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (24, 'uploads/images/20210623173406455837620.png', 5, '18,20', '绿色,64GB', 6899.00, 4633.00, 800, 1.000, 1.000, '1001236526', 2013.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (25, 'uploads/images/20210623173406455837620.png', 5, '18,21', '绿色,128GB', 6899.00, 4633.00, 800, 1.000, 1.000, '1001236526', 2013.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (26, 'uploads/images/20210623173406455837620.png', 5, '18,22', '绿色,256GB', 6899.00, 4633.00, 800, 1.000, 1.000, '1001236526', 2013.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (27, 'uploads/images/20210623173406455837620.png', 5, '19,20', '银色,64GB', 6899.00, 4633.00, 800, 1.000, 1.000, '1001236526', 2013.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (28, 'uploads/images/20210623173406455837620.png', 5, '19,21', '银色,128GB', 6899.00, 4633.00, 800, 1.000, 1.000, '1001236526', 2013.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (29, 'uploads/images/20210623173406455837620.png', 5, '19,22', '银色,256GB', 6899.00, 4633.00, 800, 1.000, 1.000, '1001236526', 2013.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (30, '/uploads/images/20210623173843e29a87344.png', 6, '23,26', '银色,128GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (31, '/uploads/images/20210623173843e29a87344.png', 6, '23,27', '银色,256GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (32, '/uploads/images/20210623173843e29a87344.png', 6, '23,28', '银色,512GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (33, '/uploads/images/20210623173843e29a87344.png', 6, '24,26', '黑色,128GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (34, '/uploads/images/20210623173843e29a87344.png', 6, '24,27', '黑色,256GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (35, '/uploads/images/20210623173843e29a87344.png', 6, '24,28', '黑色,512GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (36, '/uploads/images/20210623173843e29a87344.png', 6, '25,26', '金色,128GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (37, '/uploads/images/20210623173843e29a87344.png', 6, '25,27', '金色,256GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (38, '/uploads/images/20210623173843e29a87344.png', 6, '25,28', '金色,512GB', 5000.00, 3160.00, 800, 1.000, 1.000, '100121363', 2000.00);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (39, '/uploads/images/20210623181638488155169.png', 7, '29', '默认', 9999.00, 6666.00, 1020, 1.000, 1.000, '100120366', NULL);

INSERT INTO `ls_goods_item`(`id`, `image`, `goods_id`, `spec_value_ids`, `spec_value_str`, `market_price`, `price`, `stock`, `volume`, `weight`, `bar_code`, `chengben_price`) VALUES (40, '/uploads/images/20210623182241cd7c65317.png', 8, '30', '默认', 149.00, 149.00, 1023, 1.000, 1.000, '100210336', 66.00);

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (1, 1, ' 颜色');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (2, 1, '存储容量');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (3, 2, '默认');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (4, 3, ' 颜色');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (5, 3, ' 存储容量');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (6, 4, '颜色 ');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (7, 4, ' 存储容量');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (8, 5, ' 颜色 ');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (9, 5, '存储容量');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (10, 6, ' 颜色');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (11, 6, ' 存储容量');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (12, 7, '默认');

INSERT INTO `ls_goods_spec`(`id`, `goods_id`, `name`) VALUES (13, 8, '默认');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (1, 1, 1, '黑色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (2, 1, 1, '银色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (3, 1, 2, '32GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (4, 1, 2, '64GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (5, 2, 3, '默认');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (6, 3, 4, '黑色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (7, 3, 4, '银色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (8, 3, 4, '海蓝色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (9, 3, 5, '128GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (10, 3, 5, '256GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (11, 3, 5, '512GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (12, 4, 6, '黑色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (13, 4, 6, '紫色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (14, 4, 6, '绿色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (15, 4, 7, '64GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (16, 4, 7, '128GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (17, 4, 7, '256GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (18, 5, 8, '绿色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (19, 5, 8, '银色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (20, 5, 9, '64GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (21, 5, 9, '128GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (22, 5, 9, '256GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (23, 6, 10, '银色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (24, 6, 10, '黑色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (25, 6, 10, '金色');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (26, 6, 11, '128GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (27, 6, 11, '256GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (28, 6, 11, '512GB');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (29, 7, 12, '默认');

INSERT INTO `ls_goods_spec_value`(`id`, `goods_id`, `spec_id`, `value`) VALUES (30, 8, 13, '默认');

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (1, '克', 12, 1618471513, 1623308969, 0);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (2, '333', 100, 1621840474, 1623307238, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (3, '千克', 15, 1623207082, 1623308973, 0);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (4, '而沃尔沃v', 59, 1623207190, 1623207248, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (5, '天也热他', 99, 1623207262, 1623307236, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (6, '有人热天天', 98, 1623207272, 1623207403, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (7, '尔特疼UR', 95, 1623207286, 1623307234, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (8, '786tu6r头晕', 100, 1623207292, 1623207357, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (9, '太热呃呃呃额头', 48, 1623207305, 1623307228, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (10, '人突然有人头', 79, 1623207318, 1623307232, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (11, '吞吞吐吐拖拖拖拖拖拖拖拖', 66, 1623207331, 1623307230, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (12, '热热热热热若若若若若若若若若', 42, 1623207342, 1623307225, 1);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (13, '台', 1, 1623307253, 1623307253, 0);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (14, '条', 3, 1623307259, 1623308886, 0);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (15, '支', 9, 1623307270, 1623308964, 0);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (16, '包', 7, 1623308846, 1623308898, 0);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (17, '袋', 5, 1623308876, 1623308926, 0);

INSERT INTO `ls_goods_unit`(`id`, `name`, `sort`, `create_time`, `update_time`, `del`) VALUES (18, '件', 4, 1623312090, 1623312090, 0);

INSERT INTO `ls_help`(`id`, `cid`, `title`, `image`, `intro`, `content`, `visit`, `likes`, `sort`, `is_show`, `del`, `create_time`, `update_time`) VALUES (1, 1, '我们不一样', 'uploads/images/202104261537322d2d11122.png', '', '<p>我们不一样</p>', 0, 0, 10, 1, 0, 1619602187, 1623309612);

INSERT INTO `ls_help`(`id`, `cid`, `title`, `image`, `intro`, `content`, `visit`, `likes`, `sort`, `is_show`, `del`, `create_time`, `update_time`) VALUES (2, 1, '测试001', '', '', '', 0, 0, 0, 0, 1, 1624438777, 1624438794);

INSERT INTO `ls_help_category`(`id`, `name`, `is_show`, `del`, `create_time`, `update_time`) VALUES (1, '阅文', 1, 0, 1619602123, 1619602123);

INSERT INTO `ls_help_category`(`id`, `name`, `is_show`, `del`, `create_time`, `update_time`) VALUES (2, '固话发发发发发发付付付付付', 0, 1, 1623306520, 1623306595);

INSERT INTO `ls_help_category`(`id`, `name`, `is_show`, `del`, `create_time`, `update_time`) VALUES (3, '掘金刚刚', 0, 1, 1623306608, 1623306616);

INSERT INTO `ls_help_category`(`id`, `name`, `is_show`, `del`, `create_time`, `update_time`) VALUES (4, '熬到很晚就得', 1, 1, 1623380001, 1623380020);

UPDATE `ls_menu_decorate` SET `name` = '限时秒杀', `decorate_type` = 1, `image` = '/uploads/images/20210623164525c1e7b2228.png', `link_type` = 1, `link_address` = '100', `sort` = 0, `is_show` = 1, `create_time` = 1623140200, `update_time` = 1624437930, `del` = 0 WHERE `id` = 1;

UPDATE `ls_menu_decorate` SET `name` = '热销榜单', `decorate_type` = 1, `image` = '/uploads/images/20210623164525255c89351.png', `link_type` = 1, `link_address` = '102', `sort` = 0, `is_show` = 1, `create_time` = 1623140554, `update_time` = 1624437945, `del` = 0 WHERE `id` = 2;

UPDATE `ls_menu_decorate` SET `name` = '新品推荐', `decorate_type` = 1, `image` = '/uploads/images/20210623164525255c89351.png', `link_type` = 1, `link_address` = '113', `sort` = 0, `is_show` = 1, `create_time` = 1623141794, `update_time` = 1624438163, `del` = 0 WHERE `id` = 3;

UPDATE `ls_menu_decorate` SET `name` = '店铺街', `decorate_type` = 1, `image` = '/uploads/images/202106231645256f5133787.png', `link_type` = 1, `link_address` = '114', `sort` = 0, `is_show` = 1, `create_time` = 1623141824, `update_time` = 1624438147, `del` = 0 WHERE `id` = 4;

UPDATE `ls_menu_decorate` SET `name` = '领券中心', `decorate_type` = 1, `image` = '/uploads/images/202106231645258357a4113.png', `link_type` = 1, `link_address` = '103', `sort` = 0, `is_show` = 1, `create_time` = 1623141838, `update_time` = 1624437979, `del` = 0 WHERE `id` = 5;

UPDATE `ls_menu_decorate` SET `name` = '我的收藏', `decorate_type` = 1, `image` = '/uploads/images/20210623164702b03689886.png', `link_type` = 1, `link_address` = '106', `sort` = 0, `is_show` = 1, `create_time` = 1623141930, `update_time` = 1624438024, `del` = 0 WHERE `id` = 6;

UPDATE `ls_menu_decorate` SET `name` = '商城资讯', `decorate_type` = 1, `image` = '/uploads/images/2021062316452596c500284.png', `link_type` = 1, `link_address` = '107', `sort` = 0, `is_show` = 1, `create_time` = 1623141946, `update_time` = 1624438037, `del` = 0 WHERE `id` = 7;

UPDATE `ls_menu_decorate` SET `name` = '帮助中心', `decorate_type` = 1, `image` = '/uploads/images/202106231645251f6264326.png', `link_type` = 1, `link_address` = '108', `sort` = 0, `is_show` = 1, `create_time` = 1623141966, `update_time` = 1624438170, `del` = 0 WHERE `id` = 8;

UPDATE `ls_menu_decorate` SET `name` = '超级拼团', `decorate_type` = 1, `image` = '/uploads/images/2021062316452506ff00107.png', `link_type` = 1, `link_address` = '101', `sort` = 2, `is_show` = 1, `create_time` = 1623142056, `update_time` = 1624438107, `del` = 0 WHERE `id` = 9;

UPDATE `ls_menu_decorate` SET `name` = '会员等级', `decorate_type` = 2, `image` = '/uploads/images/202106231653409f6a76923.png', `link_type` = 1, `link_address` = '203', `sort` = 0, `is_show` = 1, `create_time` = 1623146068, `update_time` = 1624438426, `del` = 0 WHERE `id` = 10;

UPDATE `ls_menu_decorate` SET `name` = '分销推广', `decorate_type` = 2, `image` = '/uploads/images/2021062316561070f0b5417.png', `link_type` = 1, `link_address` = '201', `sort` = 0, `is_show` = 1, `create_time` = 1623146082, `update_time` = 1624438573, `del` = 0 WHERE `id` = 11;

UPDATE `ls_menu_decorate` SET `name` = '钱包余额', `decorate_type` = 2, `image` = '/uploads/images/20210623165340a4a071710.png', `link_type` = 1, `link_address` = '200', `sort` = 0, `is_show` = 1, `create_time` = 1623146101, `update_time` = 1624438583, `del` = 0 WHERE `id` = 12;

UPDATE `ls_menu_decorate` SET `name` = '我的优惠券', `decorate_type` = 2, `image` = '/uploads/images/20210623165340e0f787843.png', `link_type` = 1, `link_address` = '202', `sort` = 0, `is_show` = 1, `create_time` = 1623146114, `update_time` = 1624438591, `del` = 0 WHERE `id` = 13;

UPDATE `ls_menu_decorate` SET `name` = '收货地址', `decorate_type` = 2, `image` = '/uploads/images/202106231653409334c6907.png', `link_type` = 1, `link_address` = '205', `sort` = 0, `is_show` = 1, `create_time` = 1623146129, `update_time` = 1624438599, `del` = 0 WHERE `id` = 14;

UPDATE `ls_menu_decorate` SET `name` = '我的收藏', `decorate_type` = 2, `image` = '/uploads/images/20210623165340f9dc75250.png', `link_type` = 1, `link_address` = '206', `sort` = 0, `is_show` = 1, `create_time` = 1623146141, `update_time` = 1624438608, `del` = 0 WHERE `id` = 15;

UPDATE `ls_menu_decorate` SET `name` = '商家入驻', `decorate_type` = 2, `image` = '/uploads/images/2021062316534022a9a0330.png', `link_type` = 1, `link_address` = '210', `sort` = 0, `is_show` = 1, `create_time` = 1623146156, `update_time` = 1624438617, `del` = 0 WHERE `id` = 16;

UPDATE `ls_menu_decorate` SET `name` = '帮助中心', `decorate_type` = 2, `image` = '/uploads/images/20210623165340bf2312516.png', `link_type` = 1, `link_address` = '204', `sort` = 0, `is_show` = 1, `create_time` = 1623146170, `update_time` = 1624438627, `del` = 0 WHERE `id` = 17;

UPDATE `ls_menu_decorate` SET `name` = '联系客服', `decorate_type` = 2, `image` = '/uploads/images/20210623165340c3afb3913.png', `link_type` = 1, `link_address` = '207', `sort` = 0, `is_show` = 1, `create_time` = 1623146203, `update_time` = 1624438635, `del` = 0 WHERE `id` = 18;

UPDATE `ls_menu_decorate` SET `name` = '消息', `decorate_type` = 2, `image` = '/uploads/images/2021062316534090ba99956.png', `link_type` = 1, `link_address` = '211', `sort` = 0, `is_show` = 1, `create_time` = 1623146220, `update_time` = 1624438643, `del` = 0 WHERE `id` = 19;

INSERT INTO `ls_recharge_template`(`id`, `money`, `give_money`, `sort`, `is_recommend`, `create_time`, `update_time`, `del`) VALUES (1, 100.00, 10.00, 100, 1, 1623291519, 1623291526, 0);

INSERT INTO `ls_recharge_template`(`id`, `money`, `give_money`, `sort`, `is_recommend`, `create_time`, `update_time`, `del`) VALUES (2, 0.01, 100.00, 100, 0, 1623291604, 1623291864, 0);

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (1, 4, 1, 1, 1000.00, 0, 1624443982, 1624444400, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (2, 4, 1, 2, 1000.00, 0, 1624443982, 1624444400, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (3, 4, 1, 3, 1000.00, 0, 1624443982, 1624444400, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (4, 4, 1, 4, 1000.00, 0, 1624443982, 1624444400, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (5, 7, 2, 5, 3560.00, 0, 1624444010, 1624444170, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (6, 5, 3, 6, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (7, 5, 3, 7, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (8, 5, 3, 8, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (9, 5, 3, 9, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (10, 5, 3, 10, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (11, 5, 3, 11, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (12, 5, 3, 12, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (13, 5, 3, 13, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (14, 5, 3, 14, 6666.00, 0, 1624444041, 1624444172, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (15, 8, 4, 15, 3201.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (16, 8, 4, 16, 3201.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (17, 8, 4, 17, 3204.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (18, 8, 4, 18, 3024.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (19, 8, 4, 19, 3245.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (20, 8, 4, 20, 3240.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (21, 8, 4, 21, 3250.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (22, 8, 4, 22, 3250.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (23, 8, 4, 23, 3260.00, 0, 1624444081, 1624444173, 1, 1, 0, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (24, 9, 2, 5, 3465.00, 0, 1624444256, 1624444412, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (25, 10, 3, 6, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (26, 10, 3, 7, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (27, 10, 3, 8, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (28, 10, 3, 9, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (29, 10, 3, 10, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (30, 10, 3, 11, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (31, 10, 3, 12, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (32, 10, 3, 13, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (33, 10, 3, 14, 6666.00, 0, 1624444279, 1624444421, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (34, 11, 4, 15, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (35, 11, 4, 16, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (36, 11, 4, 17, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (37, 11, 4, 18, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (38, 11, 4, 19, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (39, 11, 4, 20, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (40, 11, 4, 21, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (41, 11, 4, 22, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (42, 11, 4, 23, 2973.00, 0, 1624444305, 1624444432, 0, 1, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (43, 4, 8, 40, 99.00, 0, 1624444328, 1624444406, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (44, 9, 7, 39, 3625.00, 0, 1624444344, 1624444417, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (45, 10, 6, 30, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (46, 10, 6, 31, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (47, 10, 6, 32, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (48, 10, 6, 33, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (49, 10, 6, 34, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (50, 10, 6, 35, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (51, 10, 6, 36, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (52, 10, 6, 37, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (53, 10, 6, 38, 2300.00, 0, 1624444367, 1624444427, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (54, 11, 5, 24, 3260.00, 0, 1624444389, 1624444436, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (55, 11, 5, 25, 3260.00, 0, 1624444389, 1624444436, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (56, 11, 5, 26, 3260.00, 0, 1624444389, 1624444436, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (57, 11, 5, 27, 3260.00, 0, 1624444389, 1624444436, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (58, 11, 5, 28, 3260.00, 0, 1624444389, 1624444436, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_goods`(`id`, `seckill_id`, `goods_id`, `item_id`, `price`, `sales_sum`, `create_time`, `update_time`, `del`, `shop_id`, `review_status`, `review_desc`, `start_date`, `end_date`) VALUES (59, 11, 5, 29, 3260.00, 0, 1624444389, 1624444436, 0, 2, 1, '', '2021-06-23', '2030-07-23');

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (1, '00:00', '01:00', 1623035284, 1624444110, 1);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (2, '01:00', '02:56', 1623035301, 1623035327, 1);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (3, '01:00', '01:59', 1623132686, 1624444141, 1);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (4, '00:00', '12:00', 1623290656, 1624444125, 0);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (5, '13:00', '17:00', 1623295390, 1624444172, 1);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (6, '17:00', '18:00', 1623380186, 1624444146, 1);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (7, '12:02', '12:59', 1623384237, 1624444170, 1);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (8, '18:01', '23:00', 1623391743, 1624444173, 1);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (9, '12:01', '16:00', 1624444189, 1624444212, 0);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (10, '16:01', '20:00', 1624444205, 1624444205, 0);

INSERT INTO `ls_seckill_time`(`id`, `start_time`, `end_time`, `create_time`, `update_time`, `del`) VALUES (11, '20:01', '23:59', 1624444228, 1624444228, 0);

INSERT INTO `ls_shop`(`id`, `cid`, `type`, `name`, `nickname`, `mobile`, `wallet`, `logo`, `background`, `license`, `keywords`, `intro`, `weight`, `trade_service_fee`, `weekdays`, `service_mobile`, `province_id`, `city_id`, `district_id`, `address`, `longitude`, `latitude`, `refund_address`, `is_run`, `is_freeze`, `is_product_audit`, `is_recommend`, `del`, `expire_time`, `create_time`, `update_time`, `score`, `star`, `visited_num`, `is_distribution`) VALUES (1, 1, 1, '6666', '天才小熊猫', '13467913467', 0.00, '/uploads/images/202106231553528575a5046.png', '/uploads/images/2021062316011618df53902.jpg', '', '6666', '这是一家专注于数码领域的独角兽公司，我们的征途的星辰大海！！！', 0, 10.00, '1,2,3,4,5', '13467913467', 440000, 440100, 440104, '', '113.273423', '23.13526', '{\"nickname\":\"天才小熊猫\",\"mobile\":\"134657913467\",\"province_id\":\"440000\",\"city_id\":\"440100\",\"district_id\":\"440104\",\"address\":\"越秀1号\"}', 1, 0, 0, 0, 0, 1908374400, 1624434947, 1624435444, 0, 0, 0, 0);

INSERT INTO `ls_shop`(`id`, `cid`, `type`, `name`, `nickname`, `mobile`, `wallet`, `logo`, `background`, `license`, `keywords`, `intro`, `weight`, `trade_service_fee`, `weekdays`, `service_mobile`, `province_id`, `city_id`, `district_id`, `address`, `longitude`, `latitude`, `refund_address`, `is_run`, `is_freeze`, `is_product_audit`, `is_recommend`, `del`, `expire_time`, `create_time`, `update_time`, `score`, `star`, `visited_num`, `is_distribution`) VALUES (2, 1, 2, '大G数码集团', '天才小熊猫', '13467913467', 0.00, '/uploads/images/20210623171320939155531.png', '/uploads/images/20210623160131eef1f5848.jpg', '', '7777', '这是一家专注于数码领域的独角兽公司，我们的征途的星辰大海！！！', 0, 11.00, '', '', 0, 0, 0, '', '', '', NULL, 1, 0, 0, 0, 0, 1908374400, 1624435024, 1624439603, 0, 0, 0, 0);

INSERT INTO `ls_shop_admin`(`id`, `root`, `shop_id`, `name`, `account`, `password`, `salt`, `role_id`, `create_time`, `update_time`, `login_time`, `login_ip`, `disable`, `del`) VALUES (1, 1, 1, '超级管理员', '666666', '71b9da547d856f554bc05d348644a83d', 'b833', 0, 1624434947, 1624434947, 1624440156, '172.23.0.1', 0, 0);

INSERT INTO `ls_shop_admin`(`id`, `root`, `shop_id`, `name`, `account`, `password`, `salt`, `role_id`, `create_time`, `update_time`, `login_time`, `login_ip`, `disable`, `del`) VALUES (2, 1, 2, '超级管理员', '777777', '2b77fc576316a53d1fa2285ea2b5c660', '613f', 0, 1624435024, 1624435024, 1624440128, '172.23.0.1', 0, 0);

INSERT INTO `ls_shop_bank`(`id`, `shop_id`, `name`, `branch`, `nickname`, `account`, `del`, `create_time`, `update_time`) VALUES (1, 1, '中国工商银行', '北京路支行', '张三', '123456789123456', 0, 1624435481, 1624435481);

INSERT INTO `ls_shop_category`(`id`, `name`, `image`, `sort`, `del`, `create_time`, `update_time`) VALUES (1, '数码', 'uploads/images/2021062315451692ae64452.png', 0, 0, 1624434320, 1624434320);

INSERT INTO `ls_shop_goods_category`(`id`, `name`, `sort`, `is_show`, `image`, `remark`, `create_time`, `update_time`, `del`, `shop_id`) VALUES (1, '数码产品', 0, 1, 'uploads/images/20210623160614470f59417.png', '', 1624435580, 1624435580, 0, 1);

INSERT INTO `ls_shop_goods_category`(`id`, `name`, `sort`, `is_show`, `image`, `remark`, `create_time`, `update_time`, `del`, `shop_id`) VALUES (2, '电子数码', 0, 1, 'uploads/images/202106231720103ae858398.png', '', 1624440022, 1624440022, 0, 2);

INSERT INTO `ls_shop_stat`(`id`, `shop_id`, `ip`, `count`, `create_time`) VALUES (1, 1, '172.23.0.1', 1, 1624437286);

INSERT INTO `ls_shop_stat`(`id`, `shop_id`, `ip`, `count`, `create_time`) VALUES (2, 2, '172.23.0.1', 1, 1624443796);

INSERT INTO `ls_treaty`(`id`, `type`, `name`, `content`, `create_time`, `update_time`) VALUES (1, 10, '入驻协议', '', 1619421319, 1623377047);

INSERT INTO `ls_user_tag`(`id`, `name`, `remark`, `create_time`, `update_time`, `del`) VALUES (1, '高消费用户', '有钱', 1622198169, 1622198169, 0);

INSERT INTO `ls_user_tag`(`id`, `name`, `remark`, `create_time`, `update_time`, `del`) VALUES (2, '美妆用户', '女生', 1622198181, 1622198193, 1);

INSERT INTO `ls_user_tag`(`id`, `name`, `remark`, `create_time`, `update_time`, `del`) VALUES (3, '美妆用户', '爱美', 1622198217, 1622198217, 0);

INSERT INTO `ls_user_tag`(`id`, `name`, `remark`, `create_time`, `update_time`, `del`) VALUES (4, '订单', '我都是', 1623235448, 1623235489, 1);

INSERT INTO `ls_user_tag`(`id`, `name`, `remark`, `create_time`, `update_time`, `del`) VALUES (5, '回去唔去我好奇', '', 1623289422, 1623289437, 1);

SET FOREIGN_KEY_CHECKS = 1;