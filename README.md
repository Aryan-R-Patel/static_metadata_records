# Static Metadata Records

This is a custom Drupal module for fetching XML records (DC/MODS) and storing them into Plain Text (Long) string fields via Drush or Hooks.

## Installation
1. Download the module to your Drupal site.
2. Enable the module by **Extend -> Custom** or using ```drush en static_metadata_records```.

## Configuration
1. Go to **Configuration -> System -> Metadata Config Form**.
2. Fill out the configuration form based on the instructions below:
    1. **Custom Module Hooks** \
    Select the checkbox if you want to trigger hooks every time on creation/update of a node. 
    > Note: Enabling this option can be an expensive operation.
    2. **Destination Fields** \
    Using the dropdowns, select the destination fields for both DC & MODS records. \
    These are the fields where the records will be stored as Plain Text.
    3. **Content Type Specific Exclusion** 
        1. **Content Type to Filter** \
        Select all the content types that you ***DON'T*** want to process.
        2. **Field Machine Name & Value** \
        Enter the field machine name and value to exclude. \
        Note that any node matching the *Content Type* and containing the field with the specified value will ***NOT*** be added to the processing queue.
3. **Save** the configuration form.

## Usage
To populate the fields with plain text DC and MODS records, the nodes first need to be added to the processing queue. This can be done via 2 methods:
1. **Drush Command** (uses a csv file containing node ID's): \
Go to the terminal of your Drupal Container in Docker and execute the following command: ```drush metadata-records:metadataRecords --file=full/path/to/csv --uid=USERID``` by replacing the *--file* and *--uid* options with your actual file paths and user ID's.

2. **Hooks**: \
In your Drupal site, whenever you create/update a node, if the hooks checkbox is enabled in the configuration form, then no further setup is required. Clicking 'Save' will automatically add the node to the queue using the hooks.
> **Note**: Hooks only trigger for nodes of content type 'islandora_object' (Repository Item). They do not apply to other content types like 'Article'.


After the nodes are added to the queue, simply execute the queue by executing the following command:
```drush queue:run static_metadata_records_queue```.

The nodes should be processed and you should be able to see the destination fields populated with the relevant DC and MODS records.

# Additional Helpful Commands
1. Check how many items in your queue: ```drush queue:list```
2. Delete the queue: ```drush queue:delete static_metadata_records_queue```