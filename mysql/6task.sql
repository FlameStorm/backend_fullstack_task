# 1.
# Выборка по часам сколько денег потрачено на бустерпаки, по каждому бустерпаку.
# При этом, показать также сколько получили юзеры из них в эквиваленте \$.
# Выборка должна быть за месяц.

SELECT day(t.time_created) AS c_day,  hour(t.time_created) AS c_hour
     , t.`type`
     , t.entity_id
     , SUM(t.amount) AS sum_amount
     , SUM(t.likes_amount) AS sum_likes
FROM transaction_log AS t
WHERE t.time_created >= '2021-01-01' AND t.time_created < '2021-02-01'
GROUP BY c_day, c_hour
       , t.type
       , t.entity_id
ORDER BY c_day ASC, c_hour ASC
       , t.type ASC
;



# 2.
# Выборка по юзеру, сколько он пополнил средств и получил лайков,
# насколько он будет везучий так сказать :) .
# Остаток на счету \$ и лайков.

SELECT day(t.time_created) AS c_day,  hour(t.time_created) AS c_hour
     , t.user_id
     , COUNT(*) AS `count`
     , SUM(t.amount) AS sum_amount
     , SUM(t.likes_amount) AS sum_likes
FROM transaction_log AS t
WHERE t.time_created >= '2021-01-01' AND t.time_created < '2021-02-01'
GROUP BY c_day, c_hour
       , t.user_id
ORDER BY c_day ASC, c_hour ASC
       , t.user_id ASC
;



# 3.
# Задачу 1 и 2 сделать в один Mysql запрос.

SELECT day(t.time_created) AS c_day,  hour(t.time_created) AS c_hour
     , t.`type`
     , t.entity_id
     , t.user_id
     , COUNT(*) AS `count`
     , SUM(t.amount) AS sum_amount
     , SUM(t.likes_amount) AS sum_likes
FROM transaction_log AS t
WHERE t.time_created >= '2021-01-01' AND t.time_created < '2021-02-01'
GROUP BY c_day, c_hour
       , t.type
       , t.entity_id
       , t.user_id
ORDER BY c_day ASC, c_hour ASC
       , t.type ASC
       , t.user_id
;


