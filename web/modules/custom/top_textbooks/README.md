# Top Textbooks modules

## Overview

This uses solr textbook core to retrieve the course textbooks, and pulls the
availability information from catalog (Aleph X) services api. 

This module depends on the aleph_connector module to talk to Aleph X server, and uses
the Search API Index based views page to show the textbooks with availability information.

* https://catalog.umd.edu/X
* https://developers.exlibrisgroup.com/aleph/apis/Aleph-X-Services/introduction-to-aleph-x-services
* https://github.com/umd-lib/solr-textbook (Runs in a docker container)

## Setup

Enable the "Top Textbooks" module in "Manage | Extend"

## Configuration
* Aleph X configuration can be found at: admin/config/services/alephx_connector
* Views configuration can be found at: admin/structure/views/view/top-textbooks
* Search API Textbook Server configuration: admin/config/search/search-api/server/top_textbooks
* Search API Textbook Index configuration: admin/config/search/search-api/index/top_textbooks