UPDATE `pre_forum_thread` SET `stamp`='-1', `icon`='-1'; -- 清理标题图章 和标题ICON
-- UPDATE `pre_forum_thread` SET `stamp`='-1', `icon`='-1';
UPDATE `pre_forum_forum` SET `forumcolumns`='0', `catforumcolumns`='0'; 清理板块横排
UPDATE `pre_forum_forum` SET `status`='1'; -- 把所有板块全部显示出来


-- 将lephone 机型板块分版设置为 5

UPDATE `pre_forum_forum` SET `forumcolumns`='5' WHERE (`fid` IN (747,746,745,744));


-- 乐家族设置为 4

UPDATE `pre_forum_forum` SET `forumcolumns`='4' WHERE (`fid` IN (255));

UPDATE `pre_forum_forum` SET `jammer`='0' ; -- 取消所有干扰码