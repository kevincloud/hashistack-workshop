job "backup" {
  datacenters = ["dc1"]

  type = "batch"

  periodic {
    cron = "*/30 * * * *"
    prohibit_overlap = true
    time_zone = "America/New_York"
  }

  group "data" {
    task "consul" {
      driver = "exec"

      config {
        command = "/bin/consul"
        args = [
          "snapshot",
          "save",
          "backup.snap"
        ]
      }
    }
  }
}
