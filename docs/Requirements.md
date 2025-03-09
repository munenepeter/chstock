## Requirements

1 requsition orders (can have many items, which has respective quantities) 
 - purchase requsition number (id) AUTO e.g 001/2024-2025 (change to next financial yr, every 1st July)
 - department
 - items (item_id is a fk), cause we have to track this item in stock
 - this RO this items quantity (note the item's qty limit)
 - total price of the item (unit price * qty) (auto calcuated)
 - total price of the whole RO (sum of total price of the items in this RO)
 - created_at
 - updated_at
 - status - (pending, completed)


> must have it's own table, and views along with it's report
> have a r|ship with purchase order, if PO is delivered, mark the RO as complete
> track each item and it's respective quantity

2. Purchase Orders 
 - must have lpo number (id) (manual)
 - must have requsition number, (fk) from requsition orders
 - supplier number (fk)
 - status - (pending, completed, expired, extended)
 - method of procument (tender, qoutation, or direct) store actual tender no, or qoutation number or direct (does not have number)
 - add same items and their qty as in ROs, but can modify the actual qty on each item, which must be less than qty in RO (user can decide on the qty of each item to be in the PO)
 - new total of each item (considering the new qty) (new qty * unit price)
 - new total price of the whole PO (sum of total price of the items in this PO)
 - date_of_committed (manual) -- datetime, if this lpo is older than 30 days from this, warn - lpo has expired, & mark as expired

 - created_at
 - updated_at

> status is expired, alert user to communicate with supplier, if supplier extends expiry_date (30 days from date_of_committed, change status to extended, until we complete the order, by confirming all items have been deilverd)

3. ITEM 
   - name
   - unit price
   - unit of issue (eg. pkts of 50, pkts of 100, 10ltrs, pcs, etc )
   - active (active, not active)
   - created_at
   - updated_at
   - purchase limit qty (updated yearly), RO qty not surpass it, if so alert the user

4. supplier
  - name
  - email
  - phone
  - address
  - active (active, not active)
  - review (rating - if delivers on time => 70 - 100%, delays by 15 days => 50 -60%, else lower than 15 days - 20 - 40%)
   - created_at
   - updated_at


 > rating depends on all LPOs that this supplier is involved in (auto calc)



 5. supplier > lpo
   - lpo number

